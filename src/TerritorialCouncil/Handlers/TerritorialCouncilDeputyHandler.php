<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilDeputyHandler extends AbstractTerritorialCouncilElectedRepresentativeHandler
{
    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::DEPUTY;
    }

    protected function getMandateType(): string
    {
        return MandateTypeEnum::DEPUTY;
    }
}
