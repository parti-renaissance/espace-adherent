<?php

namespace App\Instance\InstanceQualityUpdater;

use App\Entity\Adherent;
use App\Entity\Instance\AdherentInstanceQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Instance\InstanceQualityEnum;

class GovernmentMemberQualityUpdater extends AbstractQualityUpdater
{
    protected function isValid(Adherent $adherent): bool
    {
        return $adherent->hasTerritorialCouncilMembership()
            && $adherent->getTerritorialCouncilMembership()->hasQuality(TerritorialCouncilQualityEnum::GOVERNMENT_MEMBER);
    }

    protected function getQuality(): string
    {
        return InstanceQualityEnum::GOVERNMENT_MEMBER;
    }

    protected function updateNewQuality(AdherentInstanceQuality $quality): void
    {
        $quality->setTerritorialCouncil(
            $quality->getAdherent()->getTerritorialCouncilMembership()->getTerritorialCouncil()
        );
    }
}
