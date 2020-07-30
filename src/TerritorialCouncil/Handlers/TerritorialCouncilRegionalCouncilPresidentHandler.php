<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Entity\UserListDefinitionEnum;

class TerritorialCouncilRegionalCouncilPresidentHandler extends AbstractTerritorialCouncilElectedRepresentativeHandler
{
    public function supports(Adherent $adherent): bool
    {
        $this->mandates = $this->mandateRepository->findByFunctionAndUserListDefinitionForAdherent(
            PoliticalFunctionNameEnum::PRESIDENT_OF_REGIONAL_COUNCIL,
            UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
            $adherent
        );

        return true;
    }

    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::REGIONAL_COUNCIL_PRESIDENT;
    }

    protected function getMandateType(): string
    {
        return '';
    }
}
