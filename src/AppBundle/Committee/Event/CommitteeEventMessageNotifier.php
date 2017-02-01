<?php

namespace AppBundle\Committee\Event;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Committee\CommitteeEvents;
use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeEventNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeEventMessageNotifier implements EventSubscriberInterface
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

    public function onCommitteeEventCreated(CommitteeEventCreatedEvent $event)
    {
        $committee = $event->getCommittee();

        $this->mailjet->sendMessage($this->createMessage(
            $this->manager->findOptinCommitteeFollowersList($committee),
            $committee,
            $event->getCommitteeEvent(),
            $event->getAuthor()
        ));
    }

    private function createMessage(
        AdherentCollection $followers,
        Committee $committee,
        CommitteeEvent $event,
        Adherent $host
    ): CommitteeEventNotificationMessage {
        $params = [
            'committee_uuid' => (string) $committee->getUuid(),
            'slug' => $event->getSlug(),
        ];

        $urlGenerator = $this->urlGenerator;

        return CommitteeEventNotificationMessage::create(
            $followers->toArray(),
            $host,
            $event,
            $this->generateUrl('app_committee_show_event', $params),
            function (Adherent $adherent) use ($urlGenerator, $params) {
                return CommitteeEventNotificationMessage::getRecipientVars(
                    $adherent->getFirstName(),
                    $urlGenerator->generate('app_committee_attend_event', $params),
                    $urlGenerator->generate('app_committee_show_event', $params)
                );
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
            CommitteeEvents::EVENT_CREATED => ['onCommitteeEventCreated', -128],
        ];
    }
}
