<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilBoroughCouncilorHandler extends AbstractTerritorialCouncilElectedRepresentativeHandler
{
    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::BOROUGH_COUNCILOR;
    }

    protected function getMandateTypes(): array
    {
        return [MandateTypeEnum::BOROUGH_COUNCIL];
    }
}
