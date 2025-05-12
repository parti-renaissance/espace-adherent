<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Scope\Scope;
use App\Scope\ScopeEnum;

class AgoraManagerScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::AGORA_MANAGER;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isPresidentOfAgora() || $adherent->isGeneralSecretaryOfAgora();
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
                array_filter(array_merge(
                    $adherent->presidentOfAgoras->toArray(),
                    $adherent->generalSecretaryOfAgoras->toArray(),
                ))
            )
        );

        return $scope;
    }
}
