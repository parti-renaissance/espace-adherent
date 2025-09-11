<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class MeetingScannerScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::MEETING_SCANNER;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->meetingScanner;
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }
}
