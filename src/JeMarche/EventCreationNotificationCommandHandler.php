<?php

namespace App\JeMarche;

use App\Committee\CommitteeEvent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Geo\Zone;
use App\Firebase\JeMarcheMessaging;
use App\PushToken\PushTokenManager;
use App\Repository\EventRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EventCreationNotificationCommandHandler implements MessageHandlerInterface
{
    private $eventRepository;
    private $pushTokenManager;
    private $messaging;

    public function __construct(
        EventRepository $eventRepository,
        PushTokenManager $pushTokenManager,
        JeMarcheMessaging $messaging
    ) {
        $this->eventRepository = $eventRepository;
        $this->pushTokenManager = $pushTokenManager;
        $this->messaging = $messaging;
    }

    public function __invoke(EventCreationNotificationCommand $command): void
    {
        $event = $this->getEvent($command->getUuid());

        if (!$event) {
            return;
        }

        $tokens = $this->pushTokenManager->findIdentifiersForEventCreation($event);

        if (empty($tokens)) {
            return;
        }

        if ($event instanceof CommitteeEvent) {
            $this->messaging->sendNotificationToDevices(
                $tokens,
                'Nouvel événement dans votre comité',
                $this->buildNotificationBody($event)
            );
        } elseif ($event instanceof DefaultEvent) {
            $this->messaging->sendNotificationToDevices(
                $tokens,
                sprintf('Nouvel événement dans le %s', $zone->getCode()),
                $this->buildNotificationBody($event)
            );
        }
    }

    private function findZoneToNotify(DefaultEvent $event): ?Zone
    {
        $boroughs = $event->getZonesOfType(Zone::BOROUGH);
        if (!empty($boroughs)) {
            return $boroughs[0];
        }

        $departments = $event->getParentZonesOfType(Zone::DEPARTMENT);
        if (!empty($departments)) {
            return $departments[0];
        }

        return null;
    }

    private function getEvent(UuidInterface $uuid): ?BaseEvent
    {
        return $this->eventRepository->findOneByUuid($uuid);
    }

    private static function formatDate(\DateTimeInterface $date, string $format): string
    {
        return (new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            $format
        ))->format($date);
    }

    private function buildNotificationBody(BaseEvent $event): string
    {
        return sprintf('%s • %s • %s',
            $event->getName(),
            self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
            $event->getInlineFormattedAddress()
        );
    }
}
