<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilDepartmentCouncilorHandler extends AbstractTerritorialCouncilElectedRepresentativeHandler
{
    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR;
    }

    protected function getMandateType(): string
    {
        return MandateTypeEnum::DEPARTMENTAL_COUNCIL;
    }
}
