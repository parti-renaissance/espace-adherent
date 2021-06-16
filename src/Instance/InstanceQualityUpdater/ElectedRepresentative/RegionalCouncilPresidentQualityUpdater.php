<?php

namespace App\Instance\InstanceQualityUpdater\ElectedRepresentative;

use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Instance\InstanceQualityEnum;

class RegionalCouncilPresidentQualityUpdater extends AbstractFunctionTypeBasedQualityUpdater
{
    protected function getFunctionCode(): string
    {
        return PoliticalFunctionNameEnum::PRESIDENT_OF_REGIONAL_COUNCIL;
    }

    protected function getQuality(): string
    {
        return InstanceQualityEnum::REGIONAL_COUNCIL_PRESIDENT;
    }
}
