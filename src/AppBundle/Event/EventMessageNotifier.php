<?php

namespace AppBundle\Event;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Events;
use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\EventNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventMessageNotifier implements EventSubscriberInterface
{
    private $mailjet;
    private $manager;
    private $urlGenerator;

    public function __construct(
        MailjetService $mailjet,
        CommitteeManager $manager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailjet = $mailjet;
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
    }

    public function onEventCreated(EventCreatedEvent $event)
    {
        $committee = $event->getCommittee();

        $this->mailjet->sendMessage($this->createMessage(
            $this->manager->getOptinCommitteeFollowers($committee),
            $committee,
            $event->getEvent(),
            $event->getAuthor()
        ));
    }

    private function createMessage(
        AdherentCollection $followers,
        Committee $committee,
        Event $event,
        Adherent $host
    ): EventNotificationMessage {
        $params = [
            'committee_uuid' => (string) $committee->getUuid(),
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

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_CREATED => ['onEventCreated', -128],
        ];
    }
}
