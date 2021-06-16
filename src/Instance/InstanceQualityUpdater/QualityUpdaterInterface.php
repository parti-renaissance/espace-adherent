<?php

namespace App\Instance\InstanceQualityUpdater;

use App\Entity\Adherent;

interface QualityUpdaterInterface
{
    public function update(Adherent $adherent): void;
}
