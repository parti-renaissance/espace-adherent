<?php

declare(strict_types=1);

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;

interface AlertProviderInterface
{
    public function getAlerts(Adherent $adherent): array;
}
