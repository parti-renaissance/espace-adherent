<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;

class TerritorialCouncilEmptyMembershipHandler extends AbstractTerritorialCouncilHandler
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasTerritorialCouncilMembership()
            ? $adherent->getTerritorialCouncilMembership()->getQualities()->isEmpty()
            : false;
    }

    public function handle(Adherent $adherent): void
    {
        $this->removeMembership($adherent, $adherent->getTerritorialCouncilMembership()->getTerritorialCouncil());
    }

    public function getPriority(): int
    {
        return 100;
    }

    protected function findTerritorialCouncils(Adherent $adherent): array
    {
        return [];
    }

    protected function getQualityName(): string
    {
        return '';
    }

    protected function getQualityZone(Adherent $adherent): string
    {
        return '';
    }
}
