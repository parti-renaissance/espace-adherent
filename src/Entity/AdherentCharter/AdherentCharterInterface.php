<?php

declare(strict_types=1);

namespace App\Entity\AdherentCharter;

use App\Entity\Adherent;

interface AdherentCharterInterface
{
    public function setAdherent(Adherent $adherent): void;
}
