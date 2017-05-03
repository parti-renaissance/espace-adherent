<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EventRegistration;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventContactMembersCommand;
use AppBundle\Event\EventInvitation;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Entity\Event;
use AppBundle\Form\ContactMembersType;
use AppBundle\Form\EventCommandType;
use AppBundle\Form\EventInvitationType;
use AppBundle\Form\EventRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/evenements/{uuid}/{slug}", requirements={"uuid": "%pattern_uuid%"})
 */
class EventController extends Controller
{
    /**
     * @Route(name="app_committee_show_event")
     * @Method("GET")
     */
    public function showAction(Event $event): Response
    {
        return $this->render('events/show.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
        ]);
    }

    /**
     * @Route("/ical", name="app_committee_event_export_ical")
     * @Method("GET")
     */
    public function exportIcalAction(Event $event): Response
    {
        return new Response(
            $this->get('jms_serializer')->serialize($event, 'ical'),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar',
                'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT.'; filename='.$event->getSlug().'.ics',
            ]
        );
    }

    /**
     * @Route("/inscription", name="app_committee_attend_event")
     * @Method("GET|POST")
     * @Entity("event", expr="repository.findOneActiveByUuid(uuid)")
     */
    public function attendAction(Request $request, Event $event): Response
    {
        if ($event->isFinished()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is finished and does not accept registrations anymore', $event->getUuid()));
        }

        $committee = $event->getCommittee();

        $command = new EventRegistrationCommand($event, $this->getUser());
        $form = $this->createForm(EventRegistrationType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.registration_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.registration.success'));

            return $this->redirectToRoute('app_committee_attend_event_confirmation', [
                'uuid' => (string) $event->getUuid(),
                'slug' => $event->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        return $this->render('events/attend.html.twig', [
            'committee_event' => $event,
            'committee' => $committee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *   path="/confirmation",
     *   name="app_committee_attend_event_confirmation",
     *   condition="request.query.has('registration')"
     * )
     * @Method("GET")
     */
    public function attendConfirmationAction(Request $request, Event $event): Response
    {
        $manager = $this->get('app.event.registration_manager');

        if (!$registration = $manager->findRegistration($uuid = $request->query->get('registration'))) {
            throw $this->createNotFoundException(sprintf('Unable to find event registration by its UUID: %s', $uuid));
        }

        if (!$registration->matches($event, $this->getUser())) {
            throw $this->createAccessDeniedException('Invalid event registration');
        }

        return $this->render('events/attend_confirmation.html.twig', [
            'committee_event' => $event,
            'committee' => $event->getCommittee(),
            'registration' => $registration,
        ]);
    }

    /**
     * @Route("/modifier", name="app_event_edit")
     * @Method("GET|POST")
     * @Entity("event", expr="repository.findOneActiveByUuid(uuid)")
     * @Security("is_granted('HOST_EVENT', event)")
     */
    public function editAction(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventCommandType::class, $command = EventCommand::createFromEvent($event));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.handler')->handleUpdate($event, $command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.update.success'));

            return $this->redirectToRoute('app_committee_show_event', [
                'uuid' => (string) $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/annuler", name="app_event_cancel")
     * @Method("GET|POST")
     * @Entity("event", expr="repository.findOneActiveByUuid(uuid)")
     * @Security("is_granted('HOST_EVENT', event)")
     */
    public function cancelAction(Request $request, Event $event): Response
    {
        $command = EventCommand::createFromEvent($event);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.handler')->handleCancel($event, $command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.cancel.success'));

            return $this->redirectToRoute('app_committee_show_event', [
                'uuid' => (string) $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/cancel.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscrits", name="app_event_registrations")
     * @Method("GET")
     * @Security("is_granted('HOST_EVENT', event)")
     */
    public function membersAction(Event $event): Response
    {
        $registrations = $this->getDoctrine()->getRepository(EventRegistration::class)->findByEvent($event);

        return $this->render('events/registrations.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'registrations' => $registrations,
        ]);
    }

    /**
     * @Route("/inscrits/exporter", name="app_event_export_members")
     * @Method("POST")
     * @Security("is_granted('HOST_EVENT', event)")
     */
    public function exportMembersAction(Request $request, Event $event): Response
    {
        if (!$this->isCsrfTokenValid('event.export_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to export members.');
        }

        $uuids = json_decode($request->request->get('exports'), true);

        if (!$uuids) {
            return $this->redirectToRoute('app_event_registrations', [
                'uuid' => $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        $repository = $this->getDoctrine()->getRepository(EventRegistration::class);
        $registrations = $repository->findByUuidAndEvent($event, $uuids);

        if (!$registrations) {
            return $this->redirectToRoute('app_event_registrations', [
                'uuid' => $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        $exported = $this->get('app.event.registration_exporter')->export($registrations);

        return new Response($exported, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inscrits-a-l-evenement.csv"',
        ]);
    }

    /**
     * @Route("/inscrits/contacter", name="app_event_contact_members")
     * @Method("POST")
     * @Security("is_granted('HOST_EVENT', event)")
     */
    public function contactMembersAction(Request $request, Event $event): Response
    {
        if (!$this->isCsrfTokenValid('event.contact_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to contact members.');
        }

        $uuids = json_decode($request->request->get('contacts', '[]'), true);

        if (!$uuids) {
            $this->addFlash('info', $this->get('translator')->trans('committee.event.contact.none'));

            return $this->redirectToRoute('app_event_registrations', [
                'uuid' => $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        $repository = $this->getDoctrine()->getRepository(EventRegistration::class);
        $registrations = $repository->findByUuidAndEvent($event, $uuids);

        $command = new EventContactMembersCommand($registrations, $this->getUser());

        $form = $this->createForm(ContactMembersType::class, $command, ['csrf_token_id' => 'event.contact_members'])
            ->add('submit', SubmitType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.contact_members_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.contact.success'));

            return $this->redirectToRoute('app_event_registrations', [
                'uuid' => $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        $uuids = array_map(function (EventRegistration $registration) {
            return $registration->getUuid()->toString();
        }, $registrations);

        return $this->render('events/contact.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'contacts' => $uuids,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/invitation", name="app_event_invite")
     * @Method("GET|POST")
     */
    public function inviteAction(Request $request, Event $event): Response
    {
        $eventInvitation = EventInvitation::createFromAdherent($this->getUser());

        $form = $this->createForm(EventInvitationType::class, $eventInvitation)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventInvitation $invitation */
            $invitation = $form->getData();

            $this->get('app.event.invitation_handler')->handle($invitation, $event);
            $request->getSession()->set('event_invitations_count', count($invitation->guests));

            return $this->redirectToRoute('app_event_invitation_sent', [
                'uuid' => $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/invitation.html.twig', [
            'committee_event' => $event,
            'invitation_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/invitation/merci", name="app_event_invitation_sent")
     * @Method("GET")
     */
    public function invitationSentAction(Request $request, Event $event): Response
    {
        if (!$invitationsCount = $request->getSession()->remove('event_invitations_count')) {
            return $this->redirectToRoute('app_event_invite', [
                'uuid' => $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/invitation_sent.html.twig', [
            'committee_event' => $event,
            'invitations_count' => $invitationsCount,
        ]);
    }
}
