<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Event;
use AppBundle\Event\EventInvitation;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationManager;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\EventInvitationType;
use AppBundle\Form\EventRegistrationType;
use AppBundle\Repository\EventRepository;
use AppBundle\Security\Http\Session\AnonymousFollowerSession;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/evenements/{slug}")
 * @Entity("event", expr="repository.findOnePublishedBySlug(slug)")
 */
class EventController extends Controller
{
    /**
     * @Route(name="app_event_show", methods={"GET"})
     */
    public function showAction(Event $event, EventRepository $eventRepository): Response
    {
        return $this->render('events/show.html.twig', [
            'event' => $event,
            'eventsNearby' => $event->isGeocoded() ? $eventRepository->findNearbyOf($event) : null,
            'committee' => $event->getCommittee(),
        ]);
    }

    /**
     * @Route("/ical", name="app_event_export_ical", methods={"GET"})
     */
    public function exportIcalAction(Event $event): Response
    {
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT.'; filename='.$event->getSlug().'.ics';

        $response = new Response($this->get('jms_serializer')->serialize($event, 'ical'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/inscription", name="app_event_attend", methods={"GET", "POST"})
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     */
    public function attendAction(Request $request, Event $event, ?UserInterface $adherent): Response
    {
        if ($event->isFinished()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is finished and does not accept registrations anymore', $event->getUuid()));
        }

        if ($event->isFull()) {
            $this->addFlash('info', 'L\'événement est complet');

            return $this->redirectToRoute('app_event_show', ['slug' => $event->getSlug()]);
        }

        if (
            $this->isGranted('IS_ANONYMOUS')
            && $authenticate = $this->get(AnonymousFollowerSession::class)->start($request)
        ) {
            return $authenticate;
        }

        $command = new EventRegistrationCommand($event, $adherent);

        $form = $this
            ->createForm(EventRegistrationType::class, $command)
            ->handleRequest($request)
        ;

        if ($adherent || ($form->isSubmitted() && $form->isValid())) {
            $this->get('app.event.registration_handler')->handle($command);
            $this->addFlash('info', 'committee.event.registration.success');

            return $this->redirectToRoute('app_event_attend_confirmation', [
                'slug' => $event->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        return $this->render('events/attend.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/confirmation",
     *     name="app_event_attend_confirmation",
     *     condition="request.query.has('registration')",
     *     methods={"GET"}
     * )
     */
    public function attendConfirmationAction(Request $request, Event $event): Response
    {
        $manager = $this->get('app.event.registration_manager');

        try {
            if (!$registration = $manager->findRegistration($uuid = $request->query->get('registration'))) {
                throw $this->createNotFoundException(sprintf('Unable to find event registration by its UUID: %s', $uuid));
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$registration->matches($event, $this->getUser())) {
            throw $this->createAccessDeniedException('Invalid event registration');
        }

        return $this->render('events/attend_confirmation.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'registration' => $registration,
        ]);
    }

    /**
     * @Route("/invitation", name="app_event_invite", methods={"GET", "POST"})
     */
    public function inviteAction(Request $request, Event $event): Response
    {
        $eventInvitation = EventInvitation::createFromAdherent(
            $this->getUser(),
            $request->request->get('g-recaptcha-response')
        );

        $form = $this->createForm(EventInvitationType::class, $eventInvitation)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventInvitation $invitation */
            $invitation = $form->getData();

            $this->get('app.event.invitation_handler')->handle($invitation, $event);
            $request->getSession()->set('event_invitations_count', \count($invitation->guests));

            return $this->redirectToRoute('app_event_invitation_sent', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/invitation.html.twig', [
            'event' => $event,
            'invitation_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/invitation/merci", name="app_event_invitation_sent", methods={"GET"})
     */
    public function invitationSentAction(Request $request, Event $event): Response
    {
        if (!$invitationsCount = $request->getSession()->remove('event_invitations_count')) {
            return $this->redirectToRoute('app_event_invite', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/invitation_sent.html.twig', [
            'event' => $event,
            'invitations_count' => $invitationsCount,
        ]);
    }

    /**
     * @Route("/desinscription", name="app_event_unregistration", condition="request.isXmlHttpRequest()", methods={"GET", "POST"})
     */
    public function unregistrationAction(
        Request $request,
        Event $event,
        EventRegistrationManager $eventRegistrationManager
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('event.unregistration', $token = $request->request->get('token'))) {
            throw new BadRequestHttpException('Invalid CSRF protection token to unregister from the citizen action.');
        }

        if (!($adherentEventRegistration = $eventRegistrationManager->searchRegistration($event, $this->getUser()->getEmailAddress(), null))) {
            throw $this->createNotFoundException(
                'Impossible d\'exécuter la désinscription de l\'évènement, votre inscription n\'est pas trouvée.'
            );
        }

        $eventRegistrationManager->remove($adherentEventRegistration);

        return new JsonResponse();
    }
}
