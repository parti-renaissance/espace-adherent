<?php

namespace App\Event;

use App\Entity\Event\BaseEvent;
use App\Firebase\JeMarcheMessaging;
use App\JeMarche\JeMarcheDeviceNotifier;
use App\JeMarche\Notification\EventReminderNotification;
use App\PushToken\PushTokenManager;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventReminderHandler
{
    private $deviceNotifier;
    private $messaging;
    private $pushTokenManager;
    private $eventRepository;
    private $entityManager;

    public function __construct(
        JeMarcheDeviceNotifier $deviceNotifier,
        JeMarcheMessaging $messaging,
        PushTokenManager $pushTokenManager,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->deviceNotifier = $deviceNotifier;
        $this->messaging = $messaging;
        $this->pushTokenManager = $pushTokenManager;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
    }

    public function findEventsToRemind(
        \DateTimeInterface $startAfter,
        \DateTimeInterface $startBefore,
        string $mode = null
    ): array {
        return $this->eventRepository->findEventsToRemind($startAfter, $startBefore, $mode);
    }

    public function scheduleReminder(BaseEvent $event): void
    {
        $this->deviceNotifier->sendEventReminder($event);
    }

    public function sendReminder(BaseEvent $event): void
    {
        $tokens = $this->pushTokenManager->findIdentifiersForEvent($event);

        if (empty($tokens)) {
            return;
        }

        $this->messaging->send(EventReminderNotification::create($tokens, $event));

        $event->setReminded(true);

        $this->entityManager->flush();
    }
}
