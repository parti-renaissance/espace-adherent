<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Campaign;
use App\Repository\Pap\VotePlaceRepository;
use App\Scope\ScopeGeneratorResolver;

class GetAvailableVotePlaceForCampaignController
{
    private VotePlaceRepository $votePlaceRepository;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        VotePlaceRepository $votePlaceRepository,
        ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
        $this->votePlaceRepository = $votePlaceRepository;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function __invoke(Campaign $campaign): array
    {
        $scope = $this->scopeGeneratorResolver->generate();
        if ($scope->isNational()) {
            return [];
        }

        return $this->votePlaceRepository->findAvailableForCampaign($campaign, $scope->getZones());
    }
}
