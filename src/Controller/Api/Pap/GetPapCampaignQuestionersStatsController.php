<?php

namespace App\Controller\Api\Pap;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Pap\Campaign;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\Request;

class GetPapCampaignQuestionersStatsController
{
    public function __invoke(
        Request $request,
        Campaign $campaign,
        AdherentRepository $adherentRepository,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ): PaginatorInterface {
        $zones = [];
        $scope = $scopeGeneratorResolver->generate();
        if (!\in_array($scope->getCode(), ScopeEnum::NATIONAL_SCOPES, true)) {
            $zones = $scope->getZones();
        }

        return $adherentRepository->findFullScoresByPapCampaign(
            $campaign,
            $zones,
            $request->query->getInt('page', 1),
            $request->query->getInt('page_size', 100)
        );
    }
}
