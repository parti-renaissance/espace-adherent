<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilCityCouncilorHandler extends AbstractTerritorialCouncilElectedRepresentativeHandler
{
    protected static function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::CITY_COUNCILOR;
    }

    protected function getMandateTypes(): array
    {
        return [MandateTypeEnum::CITY_COUNCIL];
    }
}
