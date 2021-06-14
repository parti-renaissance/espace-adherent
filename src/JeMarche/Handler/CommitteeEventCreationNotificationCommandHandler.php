<?php

namespace App\JeMarche\Handler;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Firebase\JeMarcheMessaging;
use App\JeMarche\Command\CommitteeEventCreationNotificationCommand;
use App\JeMarche\Notification\CommitteeEventCreatedNotification;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\EventRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CommitteeEventCreationNotificationCommandHandler implements MessageHandlerInterface
{
    private $eventRepository;
    private $committeeMembershipRepository;
    private $messaging;

    public function __construct(
        EventRepository $eventRepository,
        CommitteeMembershipRepository $committeeMembershipRepository,
        JeMarcheMessaging $messaging
    ) {
        $this->eventRepository = $eventRepository;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->messaging = $messaging;
    }

    public function __invoke(CommitteeEventCreationNotificationCommand $command): void
    {
        $event = $this->getEvent($command->getUuid());

        if (!$event || !$event instanceof CommitteeEvent) {
            return;
        }

        $tokens = $this->committeeMembershipRepository->findPushTokenIdentifiers($event->getCommittee());

        if (empty($tokens)) {
            return;
        }

        $this->messaging->send(CommitteeEventCreatedNotification::create($tokens, $event));
    }

    private function getEvent(UuidInterface $uuid): ?BaseEvent
    {
        return $this->eventRepository->findOneByUuid($uuid);
    }
}
