<?php

namespace App\JeMarche\Handler;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Firebase\JeMarcheMessaging;
use App\JeMarche\Command\CommitteeEventCreationNotificationCommand;
use App\JeMarche\Notification\CommitteeEventCreatedNotification;
use App\PushToken\PushTokenManager;
use App\Repository\EventRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CommitteeEventCreationNotificationCommandHandler implements MessageHandlerInterface
{
    private $eventRepository;
    private $pushTokenManager;
    private $messaging;

    public function __construct(
        EventRepository $eventRepository,
        PushTokenManager $pushTokenManager,
        JeMarcheMessaging $messaging,
    ) {
        $this->eventRepository = $eventRepository;
        $this->pushTokenManager = $pushTokenManager;
        $this->messaging = $messaging;
    }

    public function __invoke(CommitteeEventCreationNotificationCommand $command): void
    {
        $event = $this->getEvent($command->getUuid());

        if (!$event || !$event instanceof CommitteeEvent || !$committee = $event->getCommittee()) {
            return;
        }

        $tokens = $this->pushTokenManager->findIdentifiersForCommittee($committee);

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
