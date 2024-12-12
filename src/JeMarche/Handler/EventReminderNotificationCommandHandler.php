<?php

namespace App\JeMarche\Handler;

use App\Entity\Event\BaseEvent;
use App\Event\EventReminderHandler;
use App\JeMarche\Command\EventReminderNotificationCommand;
use App\Repository\Event\BaseEventRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EventReminderNotificationCommandHandler
{
    private $eventRepository;
    private $eventReminderHandler;

    public function __construct(BaseEventRepository $eventRepository, EventReminderHandler $eventReminderHandler)
    {
        $this->eventRepository = $eventRepository;
        $this->eventReminderHandler = $eventReminderHandler;
    }

    public function __invoke(EventReminderNotificationCommand $command): void
    {
        $event = $this->getEvent($command->getUuid());

        if (!$event || $event->isReminded()) {
            return;
        }

        $this->eventReminderHandler->sendReminder($event);
    }

    private function getEvent(UuidInterface $uuid): ?BaseEvent
    {
        return $this->eventRepository->findOneByUuid($uuid);
    }
}
