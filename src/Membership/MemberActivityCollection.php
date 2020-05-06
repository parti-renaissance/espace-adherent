<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\EventRegistration;

class MemberActivityCollection
{
    private const TYPE_JOINED_EN_MARCHE = 'member.activated_at';
    private const TYPE_ATTENDED_EVENT = 'member.attended_event_at';
    private const TYPE_CREATION_EVENT = 'member.creation_event_at';

    private $activities;

    /**
     * @param EventRegistration[] $eventRegistrations
     * @param BaseEvent[]         $events
     */
    public function __construct(Adherent $adherent, array $eventRegistrations = [], array $events = [])
    {
        $this->activities = [];
        if ($adherent->getRegisteredAt()) {
            $this->activities[self::formatDateAsKey($adherent->getRegisteredAt())][] = [
                'type' => self::TYPE_JOINED_EN_MARCHE,
                'log' => sprintf('A rejoint le mouvement En Marche (%s)', self::formatDate($adherent->getRegisteredAt())),
            ];
        }

        foreach ($eventRegistrations as $registration) {
            $this->activities[self::formatDateAsKey($registration->getAttendedAt())][] = [
                'type' => self::TYPE_ATTENDED_EVENT,
                'log' => sprintf('A participé à l\'événement "%s" (%s)', $registration->getEvent(), self::formatDate($registration->getAttendedAt())),
            ];
        }

        foreach ($events as $event) {
            $this->activities[self::formatDateAsKey($event->getCreatedAt())][] = [
                'type' => self::TYPE_CREATION_EVENT,
                'log' => sprintf('A créé l\'événement "%s" (%s)', $event, self::formatDate($event->getCreatedAt())),
            ];
        }

        krsort($this->activities);
    }

    public function getEventParticipationsCount(): int
    {
        $count = 0;
        foreach ($this->activities as $activity) {
            foreach ($activity as $data) {
                if (self::TYPE_ATTENDED_EVENT === $data['type']) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    public function getLogs(): \Generator
    {
        $activities = $this->activities;
        foreach ($activities as $activity) {
            foreach ($activity as $data) {
                yield $data['log'];
            }
        }
    }

    private static function formatDate(\DateTimeInterface $date): string
    {
        return $date->format('d/m/Y');
    }

    private static function formatDateAsKey(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }
}
