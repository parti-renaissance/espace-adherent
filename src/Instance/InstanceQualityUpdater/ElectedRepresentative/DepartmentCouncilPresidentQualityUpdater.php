<?php

namespace App\Instance\InstanceQualityUpdater\ElectedRepresentative;

use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Instance\InstanceQualityEnum;

class DepartmentCouncilPresidentQualityUpdater extends AbstractFunctionTypeBasedQualityUpdater
{
    protected function getFunctionCode(): string
    {
        return PoliticalFunctionNameEnum::PRESIDENT_OF_DEPARTMENTAL_COUNCIL;
    }

    protected function getQuality(): string
    {
        return InstanceQualityEnum::DEPARTMENT_COUNCIL_PRESIDENT;
    }
}
