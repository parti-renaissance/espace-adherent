<?php

namespace App\TerritorialCouncil\Handlers;

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
}
