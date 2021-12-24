<?php

namespace App\Event;

use App\Coalition\CoalitionUrlGenerator;
use App\Committee\CommitteeManager;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\EventRegistration;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\Coalition\CauseEventCreationMessage;
use App\Mailer\Message\Coalition\CoalitionsEventCancellationMessage;
use App\Mailer\Message\EventCancellationMessage;
use App\Mailer\Message\EventNotificationMessage;
use App\Mailer\Message\Message;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventMessageNotifier implements EventSubscriberInterface
{
    private $mailer;
    private $committeeManager;
    private $registrationRepository;
    private $urlGenerator;
    private $coalitionUrlGenerator;

    public function __construct(
        MailerService $transactionalMailer,
        CommitteeManager $committeeManager,
        EventRegistrationRepository $registrationRepository,
        UrlGeneratorInterface $urlGenerator,
        CoalitionUrlGenerator $coalitionUrlGenerator
    ) {
        $this->mailer = $transactionalMailer;
        $this->committeeManager = $committeeManager;
        $this->registrationRepository = $registrationRepository;
        $this->urlGenerator = $urlGenerator;
        $this->coalitionUrlGenerator = $coalitionUrlGenerator;
    }

    public function onEventCreated(EventEvent $event): void
    {
        // cause event
        $eventEvent = $event->getEvent();
        if ($eventEvent instanceof CauseEvent) {
            $chunks = array_chunk(
                $eventEvent->getCause()->getFollowers(),
                MailerService::PAYLOAD_MAXSIZE
            );

            foreach ($chunks as $chunk) {
                $this->mailer->sendMessage($this->createCauseMessage($chunk, $eventEvent));
            }
        }

        // committee event
        if (!$event instanceof CommitteeEventEvent || !$committee = $event->getCommittee()) {
            return;
        }

        $chunks = array_chunk(
            $this->committeeManager->getOptinCommitteeFollowers($committee)->toArray(),
            MailerService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->mailer->sendMessage($this->createMessage($chunk, $event->getEvent(), $event->getAuthor()));
        }
    }

    public function onEventCancelled(EventEvent $event): void
    {
        if (($event instanceof CommitteeEventEvent && !$event->getCommittee()) || !$event->getEvent()->needNotifyForCancellation()) {
            return;
        }

        if (!$event->getEvent()->isCancelled()) {
            return;
        }

        $subscriptions = $this->registrationRepository->findByEvent($event->getEvent());

        if (\count($subscriptions) > 0) {
            $chunks = array_chunk($subscriptions->toArray(), MailerService::PAYLOAD_MAXSIZE);

            foreach ($chunks as $chunk) {
                $this->mailer->sendMessage($this->createCancelMessage(
                    $chunk,
                    $event->getEvent(),
                    $event->getAuthor()
                ));
            }
        }
    }

    private function createMessage(array $followers, CommitteeEvent $event, Adherent $host): EventNotificationMessage
    {
        $params = [
            'slug' => $event->getSlug(),
        ];

        return EventNotificationMessage::create(
            $followers,
            $host,
            $event,
            $this->generateUrl('app_committee_event_show', $params),
            function (Adherent $adherent) {
                return EventNotificationMessage::getRecipientVars($adherent->getFirstName());
            }
        );
    }

    private function createCauseMessage(array $followers, CauseEvent $event): CauseEventCreationMessage
    {
        return CauseEventCreationMessage::create(
            $followers,
            $event,
            $this->coalitionUrlGenerator->generateCauseEventLink($event),
            $this->coalitionUrlGenerator->generateCauseLink($event->getCause())
        );
    }

    private function createCancelMessage(array $registered, BaseEvent $event, Adherent $host): Message
    {
        if ($event->isCoalitionsEvent()) {
            return CoalitionsEventCancellationMessage::create($registered, $event);
        } else {
            return EventCancellationMessage::create(
                $registered,
                $host,
                $event,
                $this->generateUrl('app_search_events'),
                function (EventRegistration $registration) {
                    return EventCancellationMessage::getRecipientVars($registration->getFirstName());
                }
            );
        }
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATED => ['onEventCreated', -128],
            Events::EVENT_CANCELLED => ['onEventCancelled', -128],
        ];
    }
}
