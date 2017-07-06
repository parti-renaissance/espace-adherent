<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;

class MemberActivityCollection
{
    private const TYPE_JOINED_EN_MARCHE = 'member.activated_at';
    private const TYPE_ATTENDED_EVENT = 'member.attended_event_at';

    private $activities;

    /**
     * @param Adherent            $adherent
     * @param EventRegistration[] $eventRegistrations
     */
    public function __construct(Adherent $adherent, array $eventRegistrations)
    {
        $this->activities = [];
        if ($adherent->getRegisteredAt()) {
            $this->activities[self::formatDate($adherent->getRegisteredAt())][] = [
                'type' => self::TYPE_JOINED_EN_MARCHE,
                'log' => sprintf('A rejoint le mouvement En Marche (%s)', self::formatDate($adherent->getRegisteredAt())),
            ];
        }

        foreach ($eventRegistrations as $registration) {
            $this->activities[self::formatDate($registration->getAttendedAt())][] = [
                'type' => self::TYPE_ATTENDED_EVENT,
                'log' => sprintf('A participé à l\'événement "%s" (%s)', $registration->getEvent(), self::formatDate($registration->getAttendedAt())),
            ];
        }
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
        ksort($activities);
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
}
