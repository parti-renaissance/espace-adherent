<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class PhoningScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::PHONING;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isPhoningCampaignTeamMember();
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }
}
