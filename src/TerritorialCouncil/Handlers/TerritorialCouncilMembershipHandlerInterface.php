<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;

interface TerritorialCouncilMembershipHandlerInterface
{
    public function supports(Adherent $adherent): bool;

    public function handle(Adherent $adherent): void;

    public function getPriority(): int;
}
