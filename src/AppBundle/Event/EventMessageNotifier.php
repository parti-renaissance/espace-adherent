<?php

namespace AppBundle\Event;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Events;
use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\EventCancellationMessage;
use AppBundle\Mailjet\Message\EventNotificationMessage;
use AppBundle\Membership\AdherentManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventMessageNotifier implements EventSubscriberInterface
{
    private $mailjet;
    private $committeeManager;
    private $adherentManager;
    private $urlGenerator;

    public function __construct(
        MailjetService $mailjet,
        CommitteeManager $committeeManager,
        AdherentManager $adherentManager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailjet = $mailjet;
        $this->committeeManager = $committeeManager;
        $this->adherentManager = $adherentManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function onEventCreated(EventCreatedEvent $event)
    {
        if (!$committee = $event->getCommittee()) {
            return;
        }

        $this->mailjet->sendMessage($this->createMessage(
            $this->committeeManager->getOptinCommitteeFollowers($committee),
            $event->getEvent(),
            $event->getAuthor()
        ));
    }

    public function onEventCancelled(EventCancelledEvent $event)
    {
        if (!$event->getCommittee()) {
            return;
        }

        if (!$event->getEvent()->isCancelled()) {
            return;
        }

        $this->mailjet->sendMessage($this->createCancellationMessage(
            $this->adherentManager->findByEvent($event->getEvent()),
            $event->getEvent(),
            $event->getAuthor()
        ));
    }

    private function createMessage(
        AdherentCollection $followers,
        Event $event,
        Adherent $host
    ): EventNotificationMessage {
        $params = [
            'uuid' => (string) $event->getUuid(),
            'slug' => $event->getSlug(),
        ];

        return EventNotificationMessage::create(
            $followers->toArray(),
            $host,
            $event,
            $this->generateUrl('app_committee_show_event', $params),
            $this->generateUrl('app_committee_attend_event', $params),
            function (Adherent $adherent) {
                return EventNotificationMessage::getRecipientVars($adherent->getFirstName());
            }
        );
    }

    private function createCancellationMessage(
        AdherentCollection $registeredAdherents,
        Event $event,
        Adherent $host
    ): EventCancellationMessage {
        return EventCancellationMessage::create(
            $registeredAdherents->toArray(),
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

    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_CREATED => ['onEventCreated', -128],
            Events::EVENT_CANCELLED => ['onEventCancelled', -128],
        ];
    }
}
