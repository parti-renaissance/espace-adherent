<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenInitiative\CitizenInitiativeCommand;
use AppBundle\Committee\Feed\CommitteeCitizenInitiativeMessage;
use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Skill;
use AppBundle\Event\EventInvitation;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\CitizenInitiativeType;
use AppBundle\Form\CommitteeFeedCitizenInitiativeMessageType;
use AppBundle\Form\EventInvitationType;
use AppBundle\Form\EventRegistrationType;
use AppBundle\Repository\SkillRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/initiative-citoyenne")
 * @Entity("initiative", expr="repository.findOnePublishedBySlug(slug)")
 */
class CitizenInitiativeController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Route("/", name="app_citizen_initiative_landing_page")
     * @Method("GET")
     */
    public function showLandingPageAction(): Response
    {
        return $this->render('citizen_initiative/landing_page.html.twig');
    }

    /**
     * @Route("/fonctionnalite_disponible_des_novembre", name="app_citizen_initiative_not_available")
     * @Method("GET")
     */
    public function showIfNotAuthorizedDepartementAction(): Response
    {
        return $this->render('citizen_initiative/not_available.html.twig');
    }

    /**
     * @Route("/creer", name="app_create_citizen_initiative")
     * @Method("GET|POST")
     */
    public function createCitizenInitiativeAction(Request $request, ?CitizenInitiativeCommand $command): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('page_campus');
        }

        $command = new CitizenInitiativeCommand($this->getUser());
        $form = $this->createForm(CitizenInitiativeType::class, $command)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_initiative.handler')->handle($command);

            $registrationCommand = new EventRegistrationCommand($command->getCitizenInitiative(), $this->getUser());
            $this->get('app.citizen_initiative.registration_handler')->handle($registrationCommand);

            $this->addFlash('info', 'citizen_initiative.creation.success');

            return $this->redirectToRoute('app_create_citizen_initiative');
        }

        return $this->render('citizen_initiative/add.html.twig', [
            'initiative' => $command,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="app_citizen_initiative_show")
     * @Method("GET")
     */
    public function showAction(CitizenInitiative $initiative): Response
    {
        return $this->render('citizen_initiative/show.html.twig', [
            'initiative' => $initiative,
        ]);
    }

    /**
     * @Route("/{slug}/invitation", name="app_citizen_initiative_invite")
     * @Method("GET|POST")
     */
    public function inviteAction(Request $request, CitizenInitiative $initiative): Response
    {
        $eventInvitation = EventInvitation::createFromAdherent($this->getUser());

        $form = $this->createForm(EventInvitationType::class, $eventInvitation)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventInvitation $invitation */
            $invitation = $form->getData();

            $this->get('app.citizen_initiative.invitation_handler')->handle($invitation, $initiative);
            $request->getSession()->set('citizen_initiative_invitations_count', count($invitation->guests));

            return $this->redirectToRoute('app_citizen_initiative_invitation_sent', [
                'slug' => $initiative->getSlug(),
            ]);
        }

        return $this->render('citizen_initiative/invitation.html.twig', [
            'initiative' => $initiative,
            'invitation_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/assistance_d_expert", name="app_citizen_initiative_expert_assistance")
     * @Method("GET|POST")
     */
    public function expertAssistanceNeededAction(Request $request, CitizenInitiative $initiative): Response
    {
        return $this->render('citizen_initiative/expert_assistance.html.twig', [
            'initiative' => $initiative,
        ]);
    }

    /**
     * @Route("/{slug}/ical", name="app_citizen_initiative_export_ical")
     * @Method("GET")
     */
    public function exportIcalAction(CitizenInitiative $initiative): Response
    {
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT.'; filename='.$initiative->getSlug().'.ics';

        $response = new Response($this->get('jms_serializer')->serialize($initiative, 'ical'));
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/{slug}/invitation/merci", name="app_citizen_initiative_invitation_sent")
     * @Method("GET")
     */
    public function invitationSentAction(Request $request, CitizenInitiative $initiative): Response
    {
        if (!$invitationsCount = $request->getSession()->remove('citizen_initiative_invitations_count')) {
            return $this->redirectToRoute('app_citizen_initiative_invite', [
                'slug' => $initiative->getSlug(),
            ]);
        }

        return $this->render('citizen_initiative/invitation_sent.html.twig', [
            'initiative' => $initiative,
            'invitations_count' => $invitationsCount,
        ]);
    }

    /**
     * @Route("/{slug}/inscription", name="app_citizen_initiative_attend")
     * @Method("GET|POST")
     */
    public function attendAction(Request $request, CitizenInitiative $initiative): Response
    {
        if ($initiative->isFinished()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is finished and does not accept registrations anymore', $initiative->getUuid()));
        }

        if ($initiative->isCancelled()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is cancelled and does not accept registrations anymore', $initiative->getUuid()));
        }

        $command = new EventRegistrationCommand($initiative, $this->getUser());
        $form = $this->createForm(EventRegistrationType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_initiative.registration_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('citizen_initiative.registration.success'));

            return $this->redirectToRoute('app_citizen_initiative_attend_confirmation', [
                'slug' => $initiative->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        return $this->render('citizen_initiative/attend.html.twig', [
            'initiative' => $initiative,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/partage-au-comite", name="app_citizen_initiative_committee_share")
     * @Method("GET|POST")
     * @Security("is_granted('ROLE_SUPERVISOR')")
     */
    public function shareToCommitteeAction(Request $request, CitizenInitiative $initiative)
    {
        if (empty($committees = $this->get('app.committee.manager')->getAdherentCommitteesSupervisor($this->getUser()))) {
            throw $this->createAccessDeniedException();
        }

        $committee = array_shift($committees);
        $message = new CommitteeCitizenInitiativeMessage($this->getUser(), $committee, $initiative);
        $form = $this->createForm(CommitteeFeedCitizenInitiativeMessageType::class, $message)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.committee.feed_manager')->createCitizenInitiative($message);
            if ($message->isPublished()) {
                $this->addFlash('info', $this->get('translator')->trans('committee.message_published'));
            } else {
                $this->addFlash('info', $this->get('translator')->trans('committee.message_created'));
            }

            return $this->redirect($this->get('app.committee.url_generator')->getPath('app_committee_show', $committee));
        }

        return $this->render('citizen_initiative/share_committee.html.twig', [
            'initiative' => $initiative,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *   path="/{slug}/confirmation",
     *   name="app_citizen_initiative_attend_confirmation",
     *   condition="request.query.has('registration')"
     * )
     * @Method("GET")
     */
    public function attendConfirmationAction(Request $request, CitizenInitiative $initiative): Response
    {
        $manager = $this->get('app.event.registration_manager');

        try {
            if (!$registration = $manager->findRegistration($uuid = $request->query->get('registration'))) {
                throw $this->createNotFoundException(sprintf('Unable to find event registration by its UUID: %s', $uuid));
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$registration->matches($initiative, $this->getUser())) {
            throw $this->createAccessDeniedException('Invalid event registration');
        }

        return $this->render('citizen_initiative/attend_confirmation.html.twig', [
            'initiative' => $initiative,
            'registration' => $registration,
        ]);
    }

    /**
     * @Route("/competences/autocompletion",
     *     name="app_citizen_initiative_skills_autocomplete",
     *     condition="request.isXmlHttpRequest()"
     * )
     * @Method("GET")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function skillsAutocompleteAction(Request $request)
    {
        $skills = $this->getDoctrine()->getRepository(Skill::class)->findAvailableSkillsFor(
            $this->get('sonata.core.slugify.cocur')->slugify($request->query->get('term')),
            $this->getUser(), SkillRepository::FIND_FOR_CITIZEN_INITIATIVE);

        return new JsonResponse($skills);
    }

    /**
     * @Route("/{slug}/abonner", name="app_citizen_initiative_activity_subscription", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADHERENT')")
     */
    public function activitySubscriptionAction(CitizenInitiative $initiative)
    {
        $this->get('app.activity_subscription.manager')->subscribeToAdherentActivity($this->getUser(), $initiative->getOrganizer());

        return $this->render('citizen_initiative/_activity_subscription.html.twig', [
            'initiative' => $initiative,
        ]);
    }
}
