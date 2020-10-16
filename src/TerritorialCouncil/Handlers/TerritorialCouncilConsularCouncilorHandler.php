<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilConsularCouncilorHandler extends AbstractTerritorialCouncilElectedRepresentativeHandler
{
    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::CONSULAR_COUNCILOR;
    }

    protected function getMandateTypes(): array
    {
        return [MandateTypeEnum::CONSULAR_COUNCIL];
    }

    protected function getQualityZone(Adherent $adherent): string
    {
        return $this->mandates[0]->getZone()
            ? $this->mandates[0]->getZone()->getName()
            : $this->mandates[0]->getGeoZone()->getName()
        ;
    }
}
