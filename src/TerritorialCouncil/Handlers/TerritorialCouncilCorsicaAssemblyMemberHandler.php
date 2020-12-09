<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilCorsicaAssemblyMemberHandler extends AbstractTerritorialCouncilElectedRepresentativeHandler
{
    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::CORSICA_ASSEMBLY_MEMBER;
    }

    protected function getMandateTypes(): array
    {
        return [MandateTypeEnum::CORSICA_ASSEMBLY_MEMBER];
    }
}
