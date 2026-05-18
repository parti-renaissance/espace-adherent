<?php

declare(strict_types=1);

namespace App\Adherent\Activity\Api;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Adherent\Activity\AdherentActivityLabels;
use App\Entity\Adherent\Activity\AdherentActivityFilter;

class AdherentActivityFilterProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdherentActivityFilter
    {
        return new AdherentActivityFilter(
            eventTypes: [
                'hit' => AdherentActivityLabels::asOptions(AdherentActivityLabels::HIT_EVENTS),
                'action_history' => AdherentActivityLabels::asOptions(AdherentActivityLabels::ACTION_HISTORY_EVENTS),
            ],
        );
    }
}
