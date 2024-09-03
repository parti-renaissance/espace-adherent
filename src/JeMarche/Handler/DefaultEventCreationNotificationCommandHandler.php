<?php

namespace App\JeMarche\Handler;

use App\Entity\Event\DefaultEvent;
use App\Entity\Geo\Zone;
use App\Firebase\JeMarcheMessaging;
use App\JeMarche\Command\DefaultEventCreationNotificationCommand;
use App\JeMarche\Notification\DefaultEventCreatedNotification;
use App\JeMarche\NotificationTopicBuilder;
use App\Repository\Event\BaseEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DefaultEventCreationNotificationCommandHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $eventRepository;
    private $messaging;
    private $topicBuilder;

    public function __construct(
        EntityManagerInterface $entityManager,
        BaseEventRepository $eventRepository,
        JeMarcheMessaging $messaging,
        NotificationTopicBuilder $topicBuilder,
    ) {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->messaging = $messaging;
        $this->topicBuilder = $topicBuilder;
    }

    public function __invoke(DefaultEventCreationNotificationCommand $command): void
    {
        $event = $this->getEvent($command->getUuid());

        if (!$event || !$event instanceof DefaultEvent) {
            return;
        }

        $this->entityManager->refresh($event);

        $zone = $this->findZoneToNotify($event);

        if (!$zone) {
            return;
        }

        $this->messaging->send(
            DefaultEventCreatedNotification::create(
                $this->topicBuilder->buildTopic($zone),
                $event,
                $zone
            )
        );
    }

    private function findZoneToNotify(DefaultEvent $event): ?Zone
    {
        $boroughs = $event->getZonesOfType(Zone::BOROUGH);
        if (!empty($boroughs)) {
            return current($boroughs);
        }

        $departments = $event->getParentZonesOfType(Zone::DEPARTMENT);
        if (!empty($departments)) {
            return current($departments);
        }

        return null;
    }

    private function getEvent(UuidInterface $uuid): ?DefaultEvent
    {
        return $this->eventRepository->findOneByUuid($uuid);
    }
}
