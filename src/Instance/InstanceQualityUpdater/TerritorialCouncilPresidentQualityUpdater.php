<?php

namespace App\Instance\InstanceQualityUpdater;

use App\Entity\Adherent;
use App\Entity\Instance\AdherentInstanceQuality;
use App\Instance\InstanceQualityEnum;

class TerritorialCouncilPresidentQualityUpdater extends AbstractQualityUpdater
{
    protected function isValid(Adherent $adherent): bool
    {
        return $adherent->isTerritorialCouncilPresident();
    }

    protected function getQuality(): string
    {
        return InstanceQualityEnum::TERRITORIAL_COUNCIL_PRESIDENT;
    }

    protected function updateNewQuality(AdherentInstanceQuality $quality): void
    {
        $quality->setTerritorialCouncil(
            $quality->getAdherent()->getTerritorialCouncilMembership()->getTerritorialCouncil()
        );
    }
}
