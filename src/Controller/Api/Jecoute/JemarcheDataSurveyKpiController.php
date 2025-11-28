<?php

declare(strict_types=1);

namespace App\Controller\Api\Jecoute;

use App\Repository\Jecoute\JemarcheDataSurveyRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\Request;

class JemarcheDataSurveyKpiController
{
    public function __invoke(
        Request $request,
        JemarcheDataSurveyRepository $jemarcheDataSurveyRepository,
        ScopeGeneratorResolver $resolver,
    ): array {
        $maxHistory = $request->query->getInt('max_history', 30);
        $scope = $resolver->generate();

        return $jemarcheDataSurveyRepository->findLastJemarcheDataSurvey($scope->getZones(), $maxHistory);
    }
}
