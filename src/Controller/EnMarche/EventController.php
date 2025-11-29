<?php

declare(strict_types=1);

namespace App\Controller\EnMarche;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Event\EventInvitation;
use App\Event\EventInvitationHandler;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Event\EventRegistrationManager;
use App\Exception\BadUuidRequestException;
use App\Exception\InvalidUuidException;
use App\Form\EventInvitationType;
use App\Form\EventRegistrationType;
use App\Repository\Event\EventRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use App\Serializer\Encoder\ICalEncoder;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('CAN_ACCESS_EVENT', subject: 'event')]
#[Route(path: '/evenements/{slug}', name: 'app_committee_event')]
class EventController extends AbstractController
{
    #[Route(name: '_show', methods: ['GET'])]
    public function showAction(
        #[MapEntity(expr: 'repository.findOnePublishedBySlug(slug)')]
        Event $event,
        EventRepository $eventRepository,
    ): Response {
        $params = [
            'event' => $event,
            'eventsNearby' => null,
            'committee' => null,
        ];

        $params = array_merge($params, [
            'eventsNearby' => $event->isGeocoded() ? $eventRepository->findNearbyOf($event) : null,
            'committee' => $event->getCommittee(),
        ]);

        return $this->render('events/show.html.twig', $params);
    }

    #[Route(path: '/ical', name: '_export_ical', methods: ['GET'])]
    public function exportIcalAction(
        #[MapEntity(expr: 'repository.findOnePublishedBySlug(slug)')]
        Event $event,
        SerializerInterface $serializer,
    ): Response {
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT.'; filename='.$event->getSlug().'.ics';

        $response = new Response($serializer->serialize($event, ICalEncoder::FORMAT), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/inscription-adherent', name: '_attend_adherent', methods: ['GET'])]
    public function attendAdherentAction(
        #[MapEntity(expr: 'repository.findOneActiveBySlug(slug)')]
        Event $event,
        ValidatorInterface $validator,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        if ($event->isFinished()) {
            throw $this->createNotFoundException(\sprintf('Event "%s" is finished and does not accept registrations anymore', $event->getUuid()));
        }

        if ($event->isFull()) {
            $this->addFlash('info', 'L\'événement est complet');

            return $this->redirectToRoute('app_committee_event_show', ['slug' => $event->getSlug()]);
        }

        $command = new EventRegistrationCommand($event, $adherent);
        $errors = $validator->validate($command);

        if (0 === $errors->count()) {
            $eventRegistrationCommandHandler->handle($command);
            $this->addFlash('info', 'committee.event.registration.success');

            return $this->redirectToRoute('app_committee_event_attend_confirmation', [
                'slug' => $event->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        $this->addFlash('info', $errors[0]->getMessage());

        return $this->redirectToRoute('app_committee_event_show', ['slug' => $event->getSlug()]);
    }

    #[Route(path: '/inscription', name: '_attend', methods: ['GET', 'POST'])]
    public function attendAction(
        Request $request,
        #[MapEntity(expr: 'repository.findOneActiveBySlug(slug)')]
        Event $event,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler,
        AnonymousFollowerSession $anonymousFollowerSession,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        if ($adherent instanceof Adherent) {
            return $this->redirectToRoute('app_committee_event_attend_adherent', ['slug' => $event->getSlug()]);
        }

        if ($event->isFinished()) {
            throw $this->createNotFoundException(\sprintf('Event "%s" is finished and does not accept registrations anymore', $event->getUuid()));
        }

        if ($event->isFull()) {
            $this->addFlash('info', 'L\'événement est complet');

            return $this->redirectToRoute('app_committee_event_show', ['slug' => $event->getSlug()]);
        }

        if ($this->isGranted('IS_ANONYMOUS') && $authenticate = $anonymousFollowerSession->start($request)) {
            return $authenticate;
        }

        $form = $this
            ->createForm(EventRegistrationType::class, $command = new EventRegistrationCommand($event))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRegistrationCommandHandler->handle($command);
            $this->addFlash('info', 'committee.event.registration.success');

            return $this->redirectToRoute('app_committee_event_attend_confirmation', [
                'slug' => $event->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        return $this->render('events/attend.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/confirmation', name: '_attend_confirmation', condition: "request.query.has('registration')", methods: ['GET'])]
    public function attendConfirmationAction(
        Request $request,
        #[MapEntity(expr: 'repository.findOnePublishedBySlug(slug)')]
        Event $event,
        EventRegistrationManager $manager,
    ): Response {
        try {
            if (!$registration = $manager->findRegistration($uuid = $request->query->get('registration'))) {
                throw $this->createNotFoundException(\sprintf('Unable to find event registration by its UUID: %s', $uuid));
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$registration->matches($event, $this->getUser())) {
            throw $this->createAccessDeniedException('Invalid event registration');
        }

        return $this->render('events/attend_confirmation.html.twig', [
            'event' => $event,
            'registration' => $registration,
        ]);
    }

    #[Route(path: '/invitation', name: '_invite', methods: ['GET', 'POST'])]
    public function inviteAction(
        Request $request,
        #[MapEntity(expr: 'repository.findOnePublishedBySlug(slug)')]
        Event $event,
        EventInvitationHandler $handler,
    ): Response {
        $eventInvitation = EventInvitation::createFromAdherent(
            $this->getUser(),
            $request->request->get('g-recaptcha-response')
        );

        $form = $this
            ->createForm(EventInvitationType::class, $eventInvitation, ['validation_groups' => ['Default', 'em_event_invitation']])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventInvitation $invitation */
            $invitation = $form->getData();

            $handler->handle($invitation, $event);
            $request->getSession()->set('event_invitations_count', \count($invitation->guests));

            return $this->redirectToRoute('app_committee_event_invitation_sent', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/invitation.html.twig', [
            'event' => $event,
            'invitation_form' => $form->createView(),
        ]);
    }

    #[Route(path: '/invitation/merci', name: '_invitation_sent', methods: ['GET'])]
    public function invitationSentAction(
        Request $request,
        #[MapEntity(expr: 'repository.findOnePublishedBySlug(slug)')]
        Event $event,
    ): Response {
        if (!$invitationsCount = $request->getSession()->remove('event_invitations_count')) {
            return $this->redirectToRoute('app_committee_event_invite', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/invitation_sent.html.twig', [
            'event' => $event,
            'invitations_count' => $invitationsCount,
        ]);
    }

    #[Route(path: '/desinscription', name: '_unregistration', condition: 'request.isXmlHttpRequest()', methods: ['GET', 'POST'])]
    public function unregistrationAction(
        Request $request,
        #[MapEntity(expr: 'repository.findOnePublishedBySlug(slug)')]
        Event $event,
        EventRegistrationManager $eventRegistrationManager,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('event.unregistration', $request->request->get('token'))) {
            throw new BadRequestHttpException('Invalid CSRF protection token.');
        }

        if (!($adherentEventRegistration = $eventRegistrationManager->searchRegistration($event, $this->getUser()->getEmailAddress(), null))) {
            throw $this->createNotFoundException('Impossible d\'exécuter la désinscription de l\'évènement, votre inscription n\'est pas trouvée.');
        }

        $eventRegistrationManager->remove($adherentEventRegistration);

        return new JsonResponse();
    }
}
