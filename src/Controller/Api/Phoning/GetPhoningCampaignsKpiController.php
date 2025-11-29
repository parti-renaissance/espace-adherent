<?php

declare(strict_types=1);

namespace App\Controller\Api\Phoning;

use App\Repository\Phoning\CampaignRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetPhoningCampaignsKpiController
{
    public function __invoke(
        ScopeGeneratorResolver $scopeGeneratorResolver,
        CampaignRepository $campaignRepository,
    ): array {
        $scope = $scopeGeneratorResolver->generate();

        if (!$scope) {
            throw new BadRequestHttpException('Unable to resolve scope from request.');
        }

        return $campaignRepository->findPhoningCampaignsKpi(!$scope->isNational() ? $scope->getZones() : []);
    }
}
