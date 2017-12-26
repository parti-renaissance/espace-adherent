<?php

namespace AppBundle\Event;

use AppBundle\Events;
use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\EventCancellationMessage;
use AppBundle\Mailer\Message\EventNotificationMessage;
use AppBundle\Membership\AdherentManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventMessageNotifier implements EventSubscriberInterface
{
    private $mailer;
    private $committeeManager;
    private $adherentManager;
    private $urlGenerator;

    public function __construct(
        MailerService $mailer,
        CommitteeManager $committeeManager,
        AdherentManager $adherentManager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailer = $mailer;
        $this->committeeManager = $committeeManager;
        $this->adherentManager = $adherentManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function onEventCreated(EventCreatedEvent $event): void
    {
        if (!$committee = $event->getCommittee()) {
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

    public function onEventCancelled(EventCancelledEvent $event): void
    {
        if (!$event->getCommittee()) {
            return;
        }

        if (!$event->getEvent()->isCancelled()) {
            return;
        }

        $subscriptions = $this->adherentManager->findByEvent($event->getEvent());

        if (count($subscriptions) > 0) {
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

    private function createMessage(array $followers, Event $event, Adherent $host): EventNotificationMessage
    {
        $params = [
            'uuid' => (string) $event->getUuid(),
            'slug' => $event->getSlug(),
        ];

        return EventNotificationMessage::create(
            $followers,
            $host,
            $event,
            $this->generateUrl('app_event_show', $params),
            $this->generateUrl('app_event_attend', $params),
            function (Adherent $adherent) {
                return EventNotificationMessage::getRecipientVars($adherent->getFirstName());
            }
        );
    }

    private function createCancelMessage(array $registered, Event $event, Adherent $host): EventCancellationMessage
    {
        return EventCancellationMessage::create(
            $registered,
            $host,
            $event,
            $this->generateUrl('app_search_events'),
            function (Adherent $adherent) {
                return EventCancellationMessage::getRecipientVars($adherent->getFirstName());
            }
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
