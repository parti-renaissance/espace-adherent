<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\Repository\Event\EventRepository;

class LiveAlertProvider implements AlertProviderInterface
{
    public function __construct(private readonly EventRepository $eventRepository)
    {
    }

    public function getAlerts(Adherent $adherent): array
    {
        if (!$events = $this->eventRepository->findWithLiveStream()) {
            return [];
        }

        $alerts = [];

        foreach ($events as $event) {
            $alerts[] = Alert::createLive($event->getName(), '', 'Voir', '/evenements/'.$event->getSlug());
        }

        return $alerts;
    }
}
