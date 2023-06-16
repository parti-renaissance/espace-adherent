<?php

namespace App\Controller\Renaissance\Event;

use App\Controller\CanaryControllerTrait;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Event\EventInvitation;
use App\Event\EventInvitationHandler;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Event\EventRegistrationManager;
use App\Form\EventInvitationType;
use App\Repository\EventRegistrationRepository;
use App\Serializer\Encoder\ICalEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/espace-adherent/evenements', name: 'app_renaissance_event')]
#[IsGranted('ROLE_RENAISSANCE_USER')]
class EventController extends AbstractController
{
    use CanaryControllerTrait;
    private const ITEMS_PER_PAGE = 6;

    #[Route(name: '_list', methods: ['GET'])]
    public function listAction(Request $request): Response
    {
        $this->disableInProduction();

        return $this->render('renaissance/adherent/events/list.html.twig', []);
    }

    #[Route(path: '/mes-evenements', name: '_my_events_list', methods: ['GET'])]
    public function myEventsListAction(
        Request $request,
        EventRegistrationRepository $eventRegistrationRepository
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $type = $request->query->get('type');

        return $this->render('renaissance/adherent/events/my_events/my_events_list.html.twig', [
            'past_events' => $eventRegistrationRepository->findActivityPastAdherentRegistrations($adherent, 'past' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'upcoming_events' => $eventRegistrationRepository->findActivityUpcomingAdherentRegistrations($adherent, 'upcoming' === $type ? $page : 1, self::ITEMS_PER_PAGE),
            'type' => $type,
        ]);
    }

    #[Route(path: '/{slug}/afficher', name: '_show', methods: ['GET'])]
    #[Entity('event', expr: 'repository.findOnePublishedBySlug(slug)')]
    public function showAction(BaseEvent $event): Response
    {
        return $this->render('renaissance/adherent/events/show.html.twig', ['event' => $event]);
    }

    #[Route(path: '/{slug}/inscription', name: '_registration', methods: ['GET'])]
    #[Entity('event', expr: 'repository.findOneActiveBySlug(slug)')]
    public function registrationAction(
        BaseEvent $event,
        ValidatorInterface $validator,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler,
        EventRegistrationManager $manager
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($event->isFinished()) {
            $this->addFlash('info', 'L\'événement est terminé');

            return $this->redirectToRoute('app_renaissance_event_show', ['slug' => $event->getSlug()]);
        }

        if ($event->isFull()) {
            $this->addFlash('info', 'L\'événement est complet');

            return $this->redirectToRoute('app_renaissance_event_show', ['slug' => $event->getSlug()]);
        }

        $command = new EventRegistrationCommand($event, $adherent);
        $errors = $validator->validate($command);

        if (0 === $errors->count()) {
            $eventRegistrationCommandHandler->handle($command);

            if (!$registration = $manager->findRegistration($uuid = (string) $command->getRegistrationUuid())) {
                throw $this->createNotFoundException(sprintf('Registration with uuid %s not found', $uuid));
            }

            if (!$registration->matches($event, $this->getUser())) {
                throw $this->createAccessDeniedException('Invalid event registration');
            }

            $this->addFlash('success', 'Votre inscription à cet événement est confirmée.');
        } else {
            $this->addFlash('error', $errors[0]->getMessage());
        }

        return $this->redirectToRoute('app_renaissance_event_show', ['slug' => $event->getSlug()]);
    }

    #[Route(path: '/{slug}/invitation', name: '_invitation', methods: ['GET', 'POST'])]
    #[Entity('event', expr: 'repository.findOnePublishedBySlug(slug)')]
    public function invitationAction(
        Request $request,
        BaseEvent $event,
        EventInvitationHandler $handler,
        TranslatorInterface $translator
    ): Response {
        $eventInvitation = EventInvitation::createFromAdherent(
            $this->getUser(),
            $request->request->get('frc-captcha-solution')
        );

        $form = $this
            ->createForm(EventInvitationType::class, $eventInvitation, ['validation_groups' => ['Default', 're_event_invitation']])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventInvitation $invitation */
            $invitation = $form->getData();

            $handler->handle($invitation, $event);

            $this->addFlash('success', $translator->trans('event.invitation.form.invite_sent', ['count' => \count($invitation->guests)]));

            return $this->redirectToRoute('app_renaissance_event_show', ['slug' => $event->getSlug()]);
        }

        return $this->render('renaissance/adherent/events/invitation.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{slug}/desinscription', name: '_unregistration', methods: ['GET'])]
    #[Entity('event', expr: 'repository.findOnePublishedBySlug(slug)')]
    public function unregistrationAction(BaseEvent $event, EventRegistrationManager $eventRegistrationManager): Response
    {
        if (!$adherentEventRegistration = $eventRegistrationManager->searchRegistration($event, $this->getUser()->getEmailAddress(), null)) {
            throw $this->createNotFoundException('Impossible de se désinscrire à cet évévenement. Inscription non trouvée.');
        }

        $eventRegistrationManager->remove($adherentEventRegistration);

        $this->addFlash('success', 'Votre inscription à cet événement a été annulée.');

        return $this->redirectToRoute('app_renaissance_event_show', ['slug' => $event->getSlug()]);
    }

    #[Route(path: '/{slug}/ical', name: '_ical', methods: ['GET'])]
    #[Entity('event', expr: 'repository.findOnePublishedBySlug(slug)')]
    public function icalAction(BaseEvent $event, SerializerInterface $serializer): Response
    {
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT.'; filename='.$event->getSlug().'.ics';

        $response = new Response($serializer->serialize($event, ICalEncoder::FORMAT), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
