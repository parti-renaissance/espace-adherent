<?php

namespace App\Controller\Api\AdherentMessage;

use App\Entity\Geo\Zone;
use App\Repository\MailchimpCampaignReportRepository;
use App\Scope\ScopeGeneratorResolver;

class GetAdherentMessageKpiController
{
    public function __invoke(
        MailchimpCampaignReportRepository $mailchimpCampaignReportRepository,
        ScopeGeneratorResolver $resolver
    ): array {
        $scope = $resolver->generate();

        return [
            'zones' => array_map(function (Zone $zone): string {
                return $zone->getName();
            }, $scope->getZones()),
            'local' => $mailchimpCampaignReportRepository->findLocalReportRation($scope->getMainCode(), $scope->getZones()),
            'national' => $mailchimpCampaignReportRepository->findNationalReportRatio($scope->getMainCode()),
        ];
    }
}
