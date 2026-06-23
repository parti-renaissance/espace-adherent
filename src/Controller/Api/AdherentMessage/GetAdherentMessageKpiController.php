<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\Entity\Geo\Zone;
use App\Repository\AdherentMessage\PublicationStatisticsRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\Request;

class GetAdherentMessageKpiController
{
    public function __invoke(
        Request $request,
        PublicationStatisticsRepository $publicationStatisticsRepository,
        ScopeGeneratorResolver $resolver,
    ): array {
        $maxHistory = $request->query->getInt('max_history', 30);
        $scope = $resolver->generate();

        return [
            'local' => $publicationStatisticsRepository->findLocalReportRatio($scope->getMainCode(), $scope->getZones(), $maxHistory),
            'national' => $publicationStatisticsRepository->findNationalReportRatio($scope->getMainCode(), $maxHistory),
            'zones' => array_map(function (Zone $zone): string {
                return $zone->getName();
            }, $scope->getZones()),
            'since' => new \DateTime("-$maxHistory days"),
        ];
    }
}
