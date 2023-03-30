<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Scope\Scope;
use App\Scope\ScopeEnum;

class SupervisorScopeGenerator extends AbstractScopeGenerator
{
    protected function getZones(Adherent $adherent): array
    {
        return [];
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isSupervisor();
    }

    public function getCode(): string
    {
        return ScopeEnum::SUPERVISOR;
    }

    protected function enrichAttributes(Scope $scope, Adherent $adherent): Scope
    {
        $adherent = $scope->getDelegator() ?? $adherent;

        $scope->addAttribute(
            'committees',
            array_map(
                fn (Committee $committee) => [
                    'name' => $committee->getName(),
                    'uuid' => $committee->getUuid()->toString(),
                ],
                $adherent->getSupervisedCommittees()
            )
        );

        return $scope;
    }
}
