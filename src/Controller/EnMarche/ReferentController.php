<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use AppBundle\Form\InstitutionalEventCommandType;
use AppBundle\Form\ReferentPersonLinkType;
use AppBundle\InstitutionalEvent\InstitutionalEventCommand;
use AppBundle\InstitutionalEvent\InstitutionalEventCommandHandler;
use AppBundle\Referent\ManagedCitizenProjectsExporter;
use AppBundle\Referent\ManagedCommitteesExporter;
use AppBundle\Referent\ManagedInstitutionalEventsExporter;
use AppBundle\Referent\ManagedUsersFilter;
use AppBundle\Referent\OrganizationalChartManager;
use AppBundle\Repository\CitizenProjectRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\InstitutionalEventRepository;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use AppBundle\Repository\ReferentOrganizationalChart\OrganizationalChartItemRepository;
use AppBundle\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use AppBundle\Repository\ReferentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * All the route names should start with 'app_referent_', if not you should modify AppBundle\EventListener\RecordReferentLastVisitListener.
 *
 * @Route("/espace-referent")
 */
class ReferentController extends Controller
{
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
}
