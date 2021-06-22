<?php

namespace App\Instance\InstanceQualityUpdater;

use App\Entity\Adherent;
use App\Entity\Instance\AdherentInstanceQuality;
use App\Instance\InstanceQualityEnum;

class QuatuorMemberQualityUpdater extends AbstractQualityUpdater
{
    protected function isValid(Adherent $adherent): bool
    {
        return \count($adherent->findNationalCouncilMandates(true)) > 0;
    }

    protected function getQuality(): string
    {
        return InstanceQualityEnum::QUATUOR_MEMBER;
    }

    protected function updateNewQuality(AdherentInstanceQuality $quality): void
    {
        $quality->setTerritorialCouncil(
            current($quality->getAdherent()->findNationalCouncilMandates(true))->getTerritorialCouncil()
        );
    }
}
