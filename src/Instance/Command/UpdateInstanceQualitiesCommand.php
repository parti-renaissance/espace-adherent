<?php

namespace App\Instance\Command;

use App\Entity\Adherent;

class UpdateInstanceQualitiesCommand
{
    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
}
