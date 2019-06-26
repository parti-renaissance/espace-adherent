<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Entity\Event;
use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Form\ApplicationRequest\ApplicationRequestTagsType;
use AppBundle\Form\EventCommandType;
use AppBundle\Form\InstitutionalEventCommandType;
use AppBundle\Form\ReferentPersonLinkType;
use AppBundle\InstitutionalEvent\InstitutionalEventCommand;
use AppBundle\InstitutionalEvent\InstitutionalEventCommandHandler;
use AppBundle\Referent\ManagedCitizenProjectsExporter;
use AppBundle\Referent\ManagedCommitteesExporter;
use AppBundle\Referent\ManagedEventsExporter;
use AppBundle\Referent\ManagedInstitutionalEventsExporter;
use AppBundle\Referent\ManagedUsersFilter;
use AppBundle\Referent\OrganizationalChartManager;
use AppBundle\Repository\ApplicationRequest\RunningMateRequestRepository;
use AppBundle\Repository\ApplicationRequest\VolunteerRequestRepository;
use AppBundle\Repository\CitizenProjectRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\InstitutionalEventRepository;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use AppBundle\Repository\ReferentOrganizationalChart\OrganizationalChartItemRepository;
use AppBundle\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use AppBundle\Repository\ReferentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * All the route names should start with 'app_referent_', if not you should modify AppBundle\EventListener\RecordReferentLastVisitListener.
 *
 * @Route("/espace-referent")
 */
class ReferentController extends Controller
{
    use CanaryControllerTrait;

    public const TOKEN_ID = 'referent_managed_users';

    /**
     * @Route("/utilisateurs", name="app_referent_users", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT') or is_granted('ROLE_COREFERENT')")
     */
    public function usersAction(Request $request, ReferentManagedUserRepository $repository): Response
    {
        $filter = new ManagedUsersFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_referent_users');
        }

        /** @var Adherent $referent */
        $referent = $this->getUser()->isCoReferent() ? $this->getUser()->getReferentOfReferentTeam() : $this->getUser();
        $results = $repository->search(
            $referent,
            $filter->hasToken() ? $filter : null,
            false,
            $request->query->getInt('page', 1)
        );

        $filter->setToken($this->get('security.csrf.token_manager')->getToken(self::TOKEN_ID));

        return $this->render('referent/users_list.html.twig', [
            'managedArea' => $referent->getManagedArea(),
            'filter' => $filter,
            'has_filter' => $request->query->has(ManagedUsersFilter::PARAMETER_TOKEN),
            'total_count' => $repository->countAdherentInReferentZone($referent),
            'results' => $results,
        ]);
    }

    /**
     * @Route("/evenements", name="app_referent_events", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function eventsAction(EventRepository $eventRepository, ManagedEventsExporter $eventsExporter): Response
    {
        return $this->render('referent/events_list.html.twig', [
            'managedEventsJson' => $eventsExporter->exportAsJson($eventRepository->findManagedBy($this->getUser())),
        ]);
    }

    /**
     * @Route("/evenements/creer", name="app_referent_events_create", methods={"GET", "POST"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function eventsCreateAction(Request $request, GeoCoder $geoCoder): Response
    {
        $command = new EventCommand($this->getUser());
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));
        $form = $this->createForm(EventCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Event $event */
            $event = $this->get('app.event.handler')->handle($command);

            $registrationCommand = new EventRegistrationCommand($event, $this->getUser());
            $this->get('app.event.registration_handler')->handle($registrationCommand);

            $this->addFlash('info', 'referent.event.creation.success');

            return $this->redirectToRoute('app_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('referent/event_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/evenements-institutionnels", name="app_referent_institutional_events", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function institutionalEventsAction(
        InstitutionalEventRepository $institutionalEventRepository,
        ManagedInstitutionalEventsExporter $exporter
    ): Response {
        return $this->render('referent/institutional_events/list.html.twig', [
            'managedInstitutionalEventsJson' => $exporter->exportAsJson(
                $institutionalEventRepository->findByOrganizer($this->getUser())
            ),
        ]);
    }

    /**
     * @Route("/evenements-institutionnels/creer", name="app_referent_institutional_events_create", methods={"GET", "POST"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function institutionalEventsCreateAction(
        Request $request,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler,
        GeoCoder $geoCoder
    ): Response {
        $command = new InstitutionalEventCommand($this->getUser());
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this
            ->createForm(InstitutionalEventCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionalEventCommandHandler->handle($command);

            $this->addFlash('info', 'referent.institutional_event.create.success');

            return $this->redirectToRoute('app_referent_institutional_events');
        }

        return $this->render('referent/institutional_events/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/evenements-institutionnels/{uuid}/editer",
     *     name="app_referent_institutional_events_edit",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET", "POST"}
     * )
     *
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_AUTHOR_OF', institutionalEvent)")
     */
    public function institutionalEventsEditAction(
        Request $request,
        InstitutionalEvent $institutionalEvent,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler
    ): Response {
        $form = $this
            ->createForm(
                InstitutionalEventCommandType::class,
                $command = InstitutionalEventCommand::createFromInstitutionalEvent($institutionalEvent),
                ['view' => InstitutionalEventCommandType::EDIT_VIEW]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionalEventCommandHandler->handleUpdate($command, $institutionalEvent);

            $this->addFlash('info', 'referent.institutional_event.update.success');

            return $this->redirectToRoute('app_referent_institutional_events');
        }

        return $this->render('referent/institutional_events/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/evenements-institutionnels/{uuid}/supprimer",
     *     name="app_referent_institutional_events_delete",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"}
     * )
     *
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_AUTHOR_OF', institutionalEvent)")
     */
    public function institutionalEventsDeleteAction(
        InstitutionalEvent $institutionalEvent,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler
    ): Response {
        $institutionalEventCommandHandler->handleDelete($institutionalEvent);

        $this->addFlash('info', 'referent.institutional_event.delete.success');

        return $this->redirectToRoute('app_referent_institutional_events');
    }

    /**
     * @Route("/comites", name="app_referent_committees", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function committeesAction(
        CommitteeRepository $committeeRepository,
        ManagedCommitteesExporter $committeesExporter
    ): Response {
        return $this->render('referent/base_group_list.html.twig', [
            'title' => 'Comités',
            'managedGroupsJson' => $committeesExporter->exportAsJson($committeeRepository->findManagedBy($this->getUser())),
        ]);
    }

    /**
     * @Route("/projets-citoyens", name="app_referent_citizen_projects", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function citizenProjectsAction(
        CitizenProjectRepository $citizenProjectRepository,
        ManagedCitizenProjectsExporter $citizenProjectsExporter
    ): Response {
        return $this->render('referent/base_group_list.html.twig', [
            'title' => 'Projets citoyens',
            'managedGroupsJson' => $citizenProjectsExporter->exportAsJson($citizenProjectRepository->findManagedByReferent($this->getUser())),
        ]);
    }

    /**
     * @Route("/mon-equipe", name="app_referent_organizational_chart", methods={"GET"})
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_ROOT_REFERENT')")
     */
    public function organizationalChartAction(
        OrganizationalChartItemRepository $organizationalChartItemRepository,
        ReferentRepository $referentRepository
    ): Response {
        return $this->render('referent/organizational_chart.html.twig', [
            'organization_chart_items' => $organizationalChartItemRepository->getRootNodes(),
            'referent' => $referentRepository->findOneByEmailAndSelectPersonOrgaChart($this->getUser()->getEmailAddress()),
        ]);
    }

    /**
     * @Route("/mon-equipe/{id}", name="app_referent_referent_person_link_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_ROOT_REFERENT')")
     */
    public function editReferentPersonLink(
        Request $request,
        ReferentPersonLinkRepository $referentPersonLinkRepository,
        ReferentRepository $referentRepository,
        PersonOrganizationalChartItem $personOrganizationalChartItem,
        OrganizationalChartManager $manager
    ) {
        $personLink = $referentPersonLinkRepository->findOrCreateByOrgaItemAndReferent(
            $personOrganizationalChartItem,
            $referentRepository->findOneByEmail($this->getUser()->getEmailAddress())
        );

        if ($request->request->has('delete')) {
            $manager->delete($personLink);

            $this->addFlash('info', 'Organigramme mis à jour.');

            return $this->redirectToRoute('app_referent_organizational_chart');
        }

        $form = $this
            ->createForm(ReferentPersonLinkType::class, $personLink)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($personLink);

            $this->addFlash('info', 'Organigramme mis à jour.');

            return $this->redirectToRoute('app_referent_organizational_chart');
        }

        return $this->render('referent/edit_referent_person_link.html.twig', [
            'form' => $form->createView(),
            'person_organizational_chart_item' => $personOrganizationalChartItem,
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers",
     *     name="app_referent_municipal_running_mate_request",
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function municipalRunningMateRequestAction(
        RunningMateRequestRepository $runningMateRequestRepository,
        UserInterface $referent
    ): Response {
        $this->disableInProduction();

        return $this->render('referent/municipal/running_mate/list.html.twig', [
            'running_mates' => $runningMateRequestRepository->findForReferent($referent),
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole",
     *     name="app_referent_municipal_volunteer_request",
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function municipalVolunteerAction(
        VolunteerRequestRepository $volunteerRequestRepository,
        UserInterface $referent
    ): Response {
        $this->disableInProduction();

        return $this->render('referent/municipal/volunteer/list.html.twig', [
            'volunteers' => $volunteerRequestRepository->findForReferent($referent),
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers/{uuid}/detail",
     *     name="app_referent_municipal_running_mate_request_detail",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function municipalRunningMateDetailAction(RunningMateRequest $runningMateRequest): Response
    {
        $this->disableInProduction();

        return $this->render('referent/municipal/running_mate/detail.html.twig', [
            'runningMateRequest' => $runningMateRequest,
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole/{uuid}/detail",
     *     name="app_referent_municipal_volunteer_request_detail",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function municipalVolunteerDetailAction(VolunteerRequest $volunteerRequest): Response
    {
        $this->disableInProduction();

        return $this->render('referent/municipal/volunteer/detail.html.twig', [
            'volunteerRequest' => $volunteerRequest,
        ]);
    }

    /**
     * @Route(
     *     path="/municipale/candidature-colistiers/{uuid}/editer-tags",
     *     name="app_referent_municipal_running_mate_request_edit_tags",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET", "POST"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function municipalRunningMateEditTagsAction(
        ObjectManager $objectManager,
        Request $request,
        RunningMateRequest $runningMateRequest
    ): Response {
        $this->disableInProduction();

        return $this->handleApplicationRequestTagsRequest(
            $objectManager,
            $request,
            $runningMateRequest,
            'referent/municipal/running_mate/edit_tags.html.twig',
            'app_referent_municipal_running_mate_request'
        );
    }

    /**
     * @Route(
     *     path="/municipale/candidature-benevole/{uuid}/editer-tags",
     *     name="app_referent_municipal_volunteer_request_edit_tags",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET", "POST"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function municipalVolunteerEditTagsAction(
        ObjectManager $objectManager,
        Request $request,
        VolunteerRequest $volunteerRequest
    ): Response {
        $this->disableInProduction();

        return $this->handleApplicationRequestTagsRequest(
            $objectManager,
            $request,
            $volunteerRequest,
            'referent/municipal/volunteer/edit_tags.html.twig',
            'app_referent_municipal_volunteer_request'
        );
    }

    private function handleApplicationRequestTagsRequest(
        ObjectManager $objectManager,
        Request $request,
        ApplicationRequest $applicationRequest,
        string $view,
        string $redirectRoute
    ): Response {
        $form = $this->createForm(ApplicationRequestTagsType::class, $applicationRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objectManager->flush();

            return $this->redirectToRoute($redirectRoute);
        }

        return $this->render($view, [
            'form' => $form->createView(),
        ]);
    }
}
