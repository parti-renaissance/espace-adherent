<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilElectedCandidateAdherentHandler extends AbstractTerritorialCouncilHandler
{
    public function supports(Adherent $adherent): bool
    {
        return false;
    }

    protected function findTerritorialCouncils(Adherent $adherent): array
    {
        return [];
    }

    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT;
    }

    protected function getQualityZone(Adherent $adherent): string
    {
        return '';
    }
}
