<?php

namespace App\Controller\EnMarche;

use App\CitizenProject\CitizenProjectCreationCommand;
use App\CitizenProject\CitizenProjectPermissions;
use App\Committee\CommitteeCreationCommand;
use App\Committee\CommitteeManager;
use App\Contact\ContactMessage;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Event;
use App\Entity\TurnkeyProject;
use App\Exception\BadUuidRequestException;
use App\Exception\EventRegistrationException;
use App\Exception\InvalidUuidException;
use App\Form\AdherentInterestsFormType;
use App\Form\CitizenProjectCommandType;
use App\Form\ContactMessageType;
use App\Form\CreateCommitteeCommandType;
use App\Geocoder\Exception\GeocodingException;
use App\Membership\MemberActivityTracker;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\Repository\AdherentRepository;
use App\Repository\CitizenProjectRepository;
use App\Repository\CommitteeRepository;
use App\Repository\EmailRepository;
use App\Repository\EventRepository;
use App\Repository\SummaryRepository;
use App\Search\SearchParametersFilter;
use App\Search\SearchResultsProvidersManager;
use App\Security\Http\Session\AnonymousFollowerSession;
use GuzzleHttp\Exception\ConnectException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-adherent")
 */
class AdherentController extends Controller
{
    /**
     * @Route("/accueil", name="app_adherent_home", methods={"GET"})
     */
    public function homeAction(
        Request $request,
        AdherentRepository $adherentRepository,
        SearchResultsProvidersManager $searchResultsProvidersManager,
        SearchParametersFilter $searchParametersFilter
    ): Response {
        $user = $this->getUser();
        $searchParametersFilter->setCity(sprintf('%s, %s', $user->getCityName(), $user->getCountryName()));
        $searchParametersFilter->setMaxResults(3);
        $searchParametersFilter->setRadius(SearchParametersFilter::RADIUS_150);
        $params = [];
        $searchParams = [SearchParametersFilter::TYPE_EVENTS, SearchParametersFilter::TYPE_COMMITTEES, SearchParametersFilter::TYPE_CITIZEN_PROJECTS];

        foreach ($searchParams as $type) {
            try {
                $searchParametersFilter->setType($type);
                $params[$type] = $searchResultsProvidersManager->find($searchParametersFilter);
            } catch (GeocodingException $exception) {
            }
        }

        if ($request->query->getBoolean('from_activation')) {
            $this->addFlash('info', 'adherent.activation.success');
        }

        return $this->render('adherent/home.html.twig', array_merge([
            'nb_adherent' => $adherentRepository->countAdherents(),
            'from_activation' => $request->query->getBoolean('from_activation'),
        ], $params));
    }

    /**
     * @Route("/tableau-de-bord", name="app_user_dashboard", methods={"GET"})
     */
    public function dashboardAction(
        AdherentRepository $adherentRepository,
        EventRepository $eventRepository,
        EmailRepository $emailRepository,
        SummaryRepository $summaryRepository,
        MemberActivityTracker $memberActivityTracker,
        UserInterface $user
    ): Response {
        return $this->render('adherent/dashboard.html.twig', [
            'events' => $eventRepository->findEventsByOrganizer($user),
            'emails' => $emailRepository->findBy(['sender' => $user->getEmailAddress()]),
            'summary' => $summaryRepository->findOneForAdherent($user),
            'activities' => $memberActivityTracker->getRecentActivitiesForAdherent($user),
            'area_stats' => $user->isReferent()
                ? [
                        'total' => $adherentRepository->countInManagedArea($user->getManagedArea()),
                        'subscriber' => $adherentRepository->countSubscriberInManagedArea($user->getManagedArea()),
                ]
                : null,
        ]);
    }

    /**
     * This action enables an adherent to pin his/her interests.
     *
     * @Route("/mon-compte/centres-d-interet", name="app_adherent_pin_interests", methods={"GET", "POST"})
     */
    public function pinInterestsAction(Request $request, EventDispatcherInterface $dispatcher): Response
    {
        $form = $this
            ->createForm(AdherentInterestsFormType::class, $adherent = $this->getUser())
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', 'adherent.update_interests.success');

            $dispatcher->dispatch(UserEvents::USER_UPDATE_INTERESTS, new UserEvent($adherent));

            return $this->redirectToRoute('app_adherent_pin_interests');
        }

        return $this->render('adherent/pin_interests.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to create a committee.
     *
     * @Route("/creer-mon-comite", name="app_adherent_create_committee", methods={"GET", "POST"})
     * @Security("is_granted('CREATE_COMMITTEE')")
     */
    public function createCommitteeAction(Request $request): Response
    {
        $command = CommitteeCreationCommand::createFromAdherent($user = $this->getUser());
        $form = $this->createForm(CreateCommitteeCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.committee.creation_handler')->handle($command);
            $this->addFlash('info', 'committee.creation.success');

            return $this->redirectToRoute('app_committee_show', ['slug' => $command->getCommittee()->getSlug()]);
        }

        return $this->render('adherent/create_committee.html.twig', [
            'form' => $form->createView(),
            'adherent' => $user,
        ]);
    }

    /**
     * This action enables an adherent to create a citizen project.
     *
     * @Route("/creer-mon-projet-citoyen/{slug}", defaults={"slug": null}, name="app_adherent_create_citizen_project", methods={"GET", "POST"})
     * @Entity("turnkeyProject", expr="repository.findOneApprovedBySlug(slug)")
     */
    public function createCitizenProjectAction(Request $request, TurnkeyProject $turnkeyProject = null): Response
    {
        if ($this->isGranted('IS_ANONYMOUS')
            && $authentication = $this->get(AnonymousFollowerSession::class)->start($request)
        ) {
            return $authentication;
        }

        $this->denyAccessUnlessGranted(CitizenProjectPermissions::CREATE);

        if ($turnkeyProject) {
            $command = CitizenProjectCreationCommand::createFromAdherentAndTurnkeyProject($user = $this->getUser(), $turnkeyProject);
        } else {
            $command = CitizenProjectCreationCommand::createFromAdherent($user = $this->getUser());
        }

        if ($name = $request->query->get('name', false)) {
            $command->name = $name;
        }
        $form = $this->createForm(CitizenProjectCommandType::class, $command, ['from_turnkey_project' => $turnkeyProject ? true : false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_project.creation_handler')->handle($command, $turnkeyProject);
            $this->addFlash('info', 'citizen_project.creation.success');

            return $this->redirectToRoute('app_citizen_project_show', ['slug' => $command->getCitizenProject()->getSlug()]);
        }

        return $this->render('adherent/create_citizen_project.html.twig', [
            'form' => $form->createView(),
            'adherent' => $user,
        ]);
    }

    /**
     * @Route("/mes-comites", name="app_adherent_committees", methods={"GET"})
     */
    public function committeesAction(CommitteeManager $manager, UserInterface $adherent): Response
    {
        return $this->render('adherent/my_activity_committees.html.twig', [
            'committeeMemberships' => $manager->getCommitteeMembershipsForAdherent($adherent),
        ]);
    }

    /**
     * @Route("/mes-evenements", name="app_adherent_events", methods={"GET"})
     */
    public function eventsAction(Request $request): Response
    {
        $manager = $this->get('app.event.registration_manager');

        try {
            $registration = $manager->getAdherentRegistrations($this->getUser(), $request->query->get('type', 'upcoming'));
        } catch (EventRegistrationException $e) {
            throw new BadRequestHttpException('Invalid request parameters.', $e);
        }

        return $this->render('adherent/my_activity_events.html.twig', [
            'registrations' => $registration,
        ]);
    }

    /**
     * @Route("/contacter/{uuid}", name="app_adherent_contact", requirements={"uuid": "%pattern_uuid%"}, methods={"GET", "POST"})
     */
    public function contactAction(Request $request, Adherent $adherent): Response
    {
        $fromType = $request->query->get('from');
        $fromId = $request->query->get('id');
        $from = null;

        try {
            if ($fromType && $fromId) {
                if ('committee' === $fromType) {
                    $from = $this->getDoctrine()->getRepository(Committee::class)->findOneByUuid($fromId);
                } elseif ('citizen_project' === $fromType) {
                    $from = $this->getDoctrine()->getRepository(CitizenProject::class)->findOneByUuid($fromId);
                } else {
                    $from = $this->getDoctrine()->getRepository(Event::class)->findOneByUuid($fromId);
                }
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        $message = ContactMessage::createWithCaptcha((string) $request->request->get('g-recaptcha-response'), $this->getUser(), $adherent);

        $form = $this->createForm(ContactMessageType::class, $message);

        try {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->get('app.adherent.contact_message_handler')->handle($message);
                $this->addFlash('info', 'adherent.contact.success');

                if ($from instanceof Committee) {
                    return $this->redirectToRoute('app_committee_show', [
                        'slug' => $from->getSlug(),
                    ]);
                }

                if ($from instanceof CitizenProject) {
                    return $this->redirectToRoute('app_citizen_project_show', [
                        'slug' => $from->getSlug(),
                    ]);
                }

                if ($from instanceof Event) {
                    return $this->redirectToRoute('app_event_show', [
                        'slug' => $from->getSlug(),
                    ]);
                }

                return $this->redirectToRoute('homepage');
            }
        } catch (ConnectException $e) {
            $this->addFlash('error_recaptcha', $this->get('translator')->trans('recaptcha.error'));
        }

        return $this->render('adherent/contact.html.twig', [
            'adherent' => $adherent,
            'form' => $form->createView(),
            'fromType' => $fromType,
            'from' => $from,
        ]);
    }

    public function listMyCommitteesAction(string $noResultMessage = null): Response
    {
        $manager = $this->get('app.committee.manager');

        return $this->render('adherent/list_my_committees.html.twig', [
            'committees' => $manager->getAdherentCommittees($this->getUser()),
            'no_result_message' => $noResultMessage,
        ]);
    }

    public function listCommitteesAlAction(CommitteeRepository $repository): Response
    {
        return $this->render('adherent/list_my_committees_al.html.twig', [
            'committees' => $repository->findCommitteesByPrivilege(
                $this->getUser(),
                CommitteeMembership::getHostPrivileges()
            ),
        ]);
    }

    public function listMyCitizenProjectsAction(
        CitizenProjectRepository $citizenProjectRepository,
        string $noResultMessage = null
    ): Response {
        return $this->render('adherent/list_my_citizen_projects.html.twig', [
            'citizen_projects' => $citizenProjectRepository->findAllRegisteredCitizenProjectsForAdherent($this->getUser()),
            'no_result_message' => $noResultMessage,
        ]);
    }

    public function listMyAdministratedCitizenProjectsAction(
        CitizenProjectRepository $citizenProjectRepository
    ): Response {
        return $this->render('adherent/list_my_administrated_citizen_projects.html.twig', [
            'administrated_citizen_projects' => $citizenProjectRepository->findAllRegisteredCitizenProjectsForAdherent($this->getUser(), true),
        ]);
    }
}
