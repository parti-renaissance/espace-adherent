<?php

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
            if ($alerts = $provider->getAlerts($adherent)) {
                $allAlerts = array_merge($allAlerts, $alerts);
            }
        }

        return $allAlerts;
    }
}
