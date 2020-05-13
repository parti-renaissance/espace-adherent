<?php

namespace App\Entity\AdherentCharter;

use App\Entity\Adherent;

interface AdherentCharterInterface
{
    public function setAdherent(Adherent $adherent): void;
}
