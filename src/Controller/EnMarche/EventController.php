<?php

namespace App\Controller\EnMarche;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Event\EventInvitation;
use App\Event\EventInvitationHandler;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Event\EventRegistrationManager;
use App\Exception\BadUuidRequestException;
use App\Exception\InvalidUuidException;
use App\Form\EventInvitationType;
use App\Form\EventRegistrationType;
use App\Repository\EventRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/evenements/{slug}", name="app_committee_event")
 * @Entity("event", expr="repository.findOnePublishedBySlug(slug)")
 */
class EventController extends AbstractController
{
    /**
     * @Route(name="_show", methods={"GET"})
     */
    public function showAction(BaseEvent $event, EventRepository $eventRepository): Response
    {
        $params = [
            'event' => $event,
            'eventsNearby' => null,
            'committee' => null,
        ];

        if ($event instanceof CommitteeEvent) {
            $params = array_merge($params, [
                'eventsNearby' => $event->isGeocoded() ? $eventRepository->findNearbyOf($event) : null,
                'committee' => $event->getCommittee(),
            ]);
        }

        return $this->render('events/show.html.twig', $params);
    }

    /**
     * @Route("/ical", name="_export_ical", methods={"GET"})
     */
    public function exportIcalAction(BaseEvent $event, SerializerInterface $serializer): Response
    {
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT.'; filename='.$event->getSlug().'.ics';

        $response = new Response($serializer->serialize($event, 'ical'), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/inscription-adherent", name="_attend_adherent", methods={"GET"})
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     *
     * @Security("is_granted('ROLE_ADHERENT')")
     */
    public function attendAdherentAction(
        BaseEvent $event,
        UserInterface $adherent,
        ValidatorInterface $validator,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler
    ): Response {
        if ($event->isFinished()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is finished and does not accept registrations anymore', $event->getUuid()));
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

    /**
     * @Route("/inscription", name="_attend", methods={"GET", "POST"})
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     */
    public function attendAction(
        Request $request,
        BaseEvent $event,
        ?UserInterface $adherent,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler,
        AnonymousFollowerSession $anonymousFollowerSession
    ): Response {
        if ($adherent) {
            return $this->redirectToRoute('app_committee_event_attend_adherent', ['slug' => $event->getSlug()]);
        }

        if ($event->isFinished()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is finished and does not accept registrations anymore', $event->getUuid()));
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

    /**
     * @Route(
     *     path="/confirmation",
     *     name="_attend_confirmation",
     *     condition="request.query.has('registration')",
     *     methods={"GET"}
     * )
     */
    public function attendConfirmationAction(
        Request $request,
        BaseEvent $event,
        EventRegistrationManager $manager
    ): Response {
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
            'registration' => $registration,
        ]);
    }

    /**
     * @Route("/invitation", name="_invite", methods={"GET", "POST"})
     */
    public function inviteAction(Request $request, BaseEvent $event, EventInvitationHandler $handler): Response
    {
        $eventInvitation = EventInvitation::createFromAdherent(
            $this->getUser(),
            $request->request->get('g-recaptcha-response')
        );

        $form = $this
            ->createForm(EventInvitationType::class, $eventInvitation)
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

    /**
     * @Route("/invitation/merci", name="_invitation_sent", methods={"GET"})
     */
    public function invitationSentAction(Request $request, BaseEvent $event): Response
    {
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

    /**
     * @Route("/desinscription", name="_unregistration", condition="request.isXmlHttpRequest()", methods={"GET", "POST"})
     */
    public function unregistrationAction(
        Request $request,
        BaseEvent $event,
        EventRegistrationManager $eventRegistrationManager
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
