<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;

interface AlertProviderInterface
{
    public function getAlert(Adherent $adherent): ?Alert;
}
