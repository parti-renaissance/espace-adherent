<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenInitiative\CitizenInitiativeCommand;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Skill;
use AppBundle\Event\EventInvitation;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\CitizenInitiativeType;
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
 * @Route("/initiative_citoyenne")
 * @Entity("citizen_initiative", expr="repository.findOnePublishedByUuid(uuid)")
 */
class CitizenInitiativeController extends Controller
{
    use EntityControllerTrait;
    use CanaryControllerTrait;

    /**
     * @Route("/creer", name="app_create_citizen_initiative")
     * @Method("GET|POST")
     * @Security("is_granted('CREATE_CITIZEN_INITIATIVE')")
     */
    public function createCitizenInitiativeAction(Request $request, ?CitizenInitiativeCommand $command): Response
    {
        $this->disableInProduction();

        $command = new CitizenInitiativeCommand($this->getUser());
        $form = $this->createForm(CitizenInitiativeType::class, $command)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_initiative.handler')->handle($command);

            $registrationCommand = new EventRegistrationCommand($command->getCitizenInitiative(), $this->getUser());
            $this->get('app.event.registration_handler')->handle($registrationCommand);

            $this->addFlash('info', 'citizen_initiative.creation.success');

            return $this->redirectToRoute('app_search_events');
        }

        return $this->render('citizen_initiative/add.html.twig', [
            'initiative' => $command,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{uuid}/{slug}", requirements={"uuid": "%pattern_uuid%"}, name="app_citizen_initiative_show")
     * @Method("GET")
     */
    public function showAction(CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        return $this->render('citizen_initiative/show.html.twig', [
            'initiative' => $initiative,
        ]);
    }

    /**
     * @Route("/{uuid}/{slug}/invitation", requirements={"uuid": "%pattern_uuid%"}, name="app_citizen_initiative_invite")
     * @Method("GET|POST")
     */
    public function inviteAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        $eventInvitation = EventInvitation::createFromAdherent($this->getUser());

        $form = $this->createForm(EventInvitationType::class, $eventInvitation)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventInvitation $invitation */
            $invitation = $form->getData();

            $this->get('app.citizen_initiative.invitation_handler')->handle($invitation, $initiative);
            $request->getSession()->set('citizen_initiative_invitations_count', count($invitation->guests));

            return $this->redirectToRoute('app_citizen_initiative_invitation_sent', [
                'uuid' => $initiative->getUuid(),
                'slug' => $initiative->getSlug(),
            ]);
        }

        return $this->render('citizen_initiative/invitation.html.twig', [
            'initiative' => $initiative,
            'invitation_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{uuid}/{slug}/assistance_d_expert", requirements={"uuid": "%pattern_uuid%"}, name="app_citizen_initiative_expert_assistance")
     * @Method("GET|POST")
     */
    public function expertAssistanceNeededAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        return $this->render('citizen_initiative/expert_assistance.html.twig', [
            'initiative' => $initiative,
        ]);
    }

    /**
     * @Route("/{uuid}/{slug}/ical", name="app_citizen_initiative_export_ical")
     * @Method("GET")
     */
    public function exportIcalAction(CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT.'; filename='.$initiative->getSlug().'.ics';

        $response = new Response($this->get('jms_serializer')->serialize($initiative, 'ical'));
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/{uuid}/{slug}/invitation/merci", name="app_citizen_initiative_invitation_sent")
     * @Method("GET")
     */
    public function invitationSentAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

        if (!$invitationsCount = $request->getSession()->remove('citizen_initiative_invitations_count')) {
            return $this->redirectToRoute('app_citizen_initiative_invite', [
                'uuid' => $initiative->getUuid(),
                'slug' => $initiative->getSlug(),
            ]);
        }

        return $this->render('citizen_initiative/invitation_sent.html.twig', [
            'initiative' => $initiative,
            'invitations_count' => $invitationsCount,
        ]);
    }

    /**
     * @Route("/{uuid}/{slug}/inscription", name="app_citizen_initiative_attend")
     * @Method("GET|POST")
     */
    public function attendAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

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
                'uuid' => (string) $initiative->getUuid(),
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
     * @Route(
     *   path="/{uuid}/{slug}/confirmation",
     *   name="app_citizen_initiative_attend_confirmation",
     *   condition="request.query.has('registration')"
     * )
     * @Method("GET")
     */
    public function attendConfirmationAction(Request $request, CitizenInitiative $initiative): Response
    {
        $this->disableInProduction();

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
     * @Security("is_granted('CREATE_CITIZEN_INITIATIVE')")
     */
    public function skillsAutocompleteAction(Request $request)
    {
        $this->disableInProduction();

        $skills = $this->getDoctrine()->getRepository(Skill::class)->findAvailableSkillsFor(
            $this->get('sonata.core.slugify.cocur')->slugify($request->query->get('term')),
            $this->getUser(), SkillRepository::FIND_FOR_CITIZEN_INITIATIVE);

        return new JsonResponse($skills);
    }

    /**
     * @Route("/{uuid}/{slug}/abonner", name="app_citizen_initiative_activity_subscription", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADHERENT')")
     */
    public function activitySubscriptionAction(CitizenInitiative $initiative)
    {
        $this->disableInProduction();

        $this->get('app.activity_subscription.manager')->subscribeToAdherentActivity($this->getUser(), $initiative->getOrganizer());

        return $this->render('citizen_initiative/_activity_subscription.html.twig', [
            'initiative' => $initiative,
        ]);
    }
}
