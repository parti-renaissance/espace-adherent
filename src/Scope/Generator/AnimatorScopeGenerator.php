<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Scope\Scope;
use App\Scope\ScopeEnum;

class AnimatorScopeGenerator extends AbstractScopeGenerator
{
    protected function getZones(Adherent $adherent): array
    {
        return [];
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isAnimator();
    }

    public function getCode(): string
    {
        return ScopeEnum::ANIMATOR;
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
                $adherent->getAnimatorCommittees()
            )
        );

        foreach ($adherent->getAnimatorCommittees() as $committee) {
            if ($dpts = $committee->getParentZonesOfType(Zone::DEPARTMENT)) {
                $scope->addAttribute('dpt', $dpts[0]->getCode());
                break;
            }
        }

        return $scope;
    }
}
