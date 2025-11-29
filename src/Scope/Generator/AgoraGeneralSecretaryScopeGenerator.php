<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Scope\Scope;
use App\Scope\ScopeEnum;

class AgoraGeneralSecretaryScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::AGORA_GENERAL_SECRETARY;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isGeneralSecretaryOfAgora();
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }

    protected function enrichAttributes(Scope $scope, Adherent $adherent): Scope
    {
        $adherent = $scope->getDelegator() ?? $adherent;

        $scope->addAttribute(
            'agoras',
            array_map(
                fn (Agora $agora) => [
                    'name' => $agora->getName(),
                    'uuid' => $agora->getUuid()->toString(),
                ],
                $adherent->generalSecretaryOfAgoras->toArray()
            )
        );

        return $scope;
    }
}
