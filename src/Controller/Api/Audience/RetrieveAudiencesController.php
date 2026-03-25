<?php

declare(strict_types=1);

namespace App\Controller\Api\Audience;

use App\Repository\Audience\AudienceRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RetrieveAudiencesController extends AbstractController
{
    public function __invoke(
        AudienceRepository $repository,
        ScopeGeneratorResolver $scopeGeneratorResolver,
    ): array {
        $scope = $scopeGeneratorResolver->generate();

        if (!$scope) {
            return [];
        }

        return $repository->findByZones($scope->getMainCode(), $scope->getZones());
    }
}
