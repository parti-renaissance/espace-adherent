<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use App\Repository\Pap\CampaignRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetPapCampaignsKpiController
{
    public function __invoke(
        ScopeGeneratorResolver $scopeGeneratorResolver,
        CampaignRepository $campaignRepository,
    ): array {
        $scope = $scopeGeneratorResolver->generate();

        if (!$scope) {
            throw new BadRequestHttpException('Unable to resolve scope from request.');
        }

        return $campaignRepository->findCampaignsKpi(!$scope->isNational() ? $scope->getZones() : []);
    }
}
