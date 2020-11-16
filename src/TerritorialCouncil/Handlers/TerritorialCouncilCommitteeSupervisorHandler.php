<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilCommitteeSupervisorHandler extends AbstractTerritorialCouncilHandler
{
    protected function findTerritorialCouncils(Adherent $adherent): array
    {
        return $this->repository->findForSupervisor($adherent);
    }

    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR;
    }

    protected function getQualityZone(Adherent $adherent): string
    {
        return $adherent->getMemberships()->getCommitteeSupervisorMemberships()->first()->getCommittee()->getName();
    }
}
