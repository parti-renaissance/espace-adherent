<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;

interface AlertProviderInterface
{
    public function getAlerts(Adherent $adherent): array;
}
