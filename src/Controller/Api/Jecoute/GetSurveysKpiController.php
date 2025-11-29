<?php

declare(strict_types=1);

namespace App\Controller\Api\Jecoute;

use App\Repository\Jecoute\LocalSurveyRepository;
use App\Repository\Jecoute\NationalSurveyRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetSurveysKpiController
{
    public function __invoke(
        ScopeGeneratorResolver $scopeGeneratorResolver,
        NationalSurveyRepository $nationalSurveyRepository,
        LocalSurveyRepository $localSurveyRepository,
    ): array {
        $scope = $scopeGeneratorResolver->generate();

        if (!$scope) {
            throw new BadRequestHttpException('Unable to resolve scope from request.');
        }

        $zones = !$scope->isNational() ? $scope->getZones() : [];

        return [
            'national_surveys_count' => $nationalSurveyRepository->count([]),
            'national_surveys_published_count' => $nationalSurveyRepository->count(['published' => true]),
            'local_surveys_count' => $localSurveyRepository->countForZones($zones),
            'local_surveys_published_count' => $localSurveyRepository->countForZones($zones, true),
        ];
    }
}
