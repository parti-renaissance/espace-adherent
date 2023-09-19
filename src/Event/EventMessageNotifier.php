<?php

namespace App\Event;

use App\AppCodeEnum;
use App\Committee\CommitteeManager;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\EventRegistration;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\EventCancellationMessage;
use App\Mailer\Message\EventNotificationMessage;
use App\Mailer\Message\JeMengage\JeMengageEventCancellationMessage;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\RenaissanceEventCancellationMessage;
use App\Mailer\Message\Renaissance\RenaissanceEventNotificationMessage;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventMessageNotifier implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly CommitteeManager $committeeManager,
        private readonly EventRegistrationRepository $registrationRepository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function onEventCreated(EventEvent $event): void
    {
        // @var BaseEvent $event
        $event = $event->getEvent();

        // committee event
        if (!$event instanceof CommitteeEvent || !$committee = $event->getCommittee()) {
            return;
        }

        $chunks = array_chunk(
            $this->committeeManager->getOptinCommitteeFollowers($committee),
            MailerService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->transactionalMailer->sendMessage($this->createMessage($chunk, $event, $event->getAuthor()));
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

        if (!$subscriptions = $this->registrationRepository->findByEvent($event->getEvent())->toArray()) {
            return;
        }

        $apps = array_unique(array_map(function (EventRegistration $eventRegistration): ?string {
            return $eventRegistration->getSource();
        }, $subscriptions));

        foreach ($apps as $appCode) {
            $recipients = array_filter($subscriptions, function (EventRegistration $eventRegistration) use ($appCode): bool {
                return $eventRegistration->getSource() === $appCode;
            });

            foreach (array_chunk($recipients, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
                $this->transactionalMailer->sendMessage($this->createCancelMessage(
                    $chunk,
                    $event->getEvent(),
                    $event->getAuthor(),
                    $appCode
                ));
            }
        }
    }

    private function createMessage(array $followers, CommitteeEvent $event, Adherent $host): Message
    {
        $params = [
            'slug' => $event->getSlug(),
        ];

        if ($event->isRenaissanceEvent()) {
            return RenaissanceEventNotificationMessage::create(
                $followers,
                $host,
                $event,
                $this->generateUrl('app_renaissance_event_show', $params),
                function (Adherent $adherent) {
                    return RenaissanceEventNotificationMessage::getRecipientVars($adherent->getFirstName());
                }
            );
        }

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

    private function createCancelMessage(array $registered, BaseEvent $event, Adherent $host, ?string $appCode): Message
    {
        if (AppCodeEnum::isJeMengage($appCode)) {
            return JeMengageEventCancellationMessage::create(
                $registered,
                $host,
                $event,
                $this->generateUrl('app_search_events')
            );
        }

        if ($event->isRenaissanceEvent()) {
            return RenaissanceEventCancellationMessage::create(
                $registered,
                $host,
                $event,
                $this->generateUrl('app_renaissance_event_list')
            );
        }

        return EventCancellationMessage::create(
            $registered,
            $host,
            $event,
            $this->generateUrl('app_search_events')
        );
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
