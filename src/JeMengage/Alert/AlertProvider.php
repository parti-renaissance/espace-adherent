<?php

declare(strict_types=1);

namespace App\JeMengage\Alert;

use App\Entity\Adherent;
use App\JeMengage\Alert\Provider\AlertProviderInterface;

class AlertProvider
{
    /** @param AlertProviderInterface[] $providers */
    public function __construct(private readonly iterable $providers)
    {
    }

    public function getAlerts(Adherent $adherent): array
    {
        $allAlerts = [];

        foreach ($this->providers as $provider) {
            $allAlerts = array_merge($allAlerts, $provider->getAlerts($adherent));
        }

        usort($allAlerts, fn (Alert $a, Alert $b) => $b->date <=> $a->date);

        return $allAlerts;
    }
}
