<?php

namespace App\Controller\Api\AdherentMessage;

use App\Entity\Geo\Zone;
use App\Repository\MailchimpCampaignReportRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\Request;

class GetAdherentMessageKpiController
{
    public function __invoke(
        Request $request,
        MailchimpCampaignReportRepository $mailchimpCampaignReportRepository,
        ScopeGeneratorResolver $resolver,
    ): array {
        $maxHistory = $request->query->getInt('max_history', 30);
        $scope = $resolver->generate();

        return [
            'local' => $mailchimpCampaignReportRepository->findLocalReportRation($scope->getMainCode(), $scope->getZones(), $maxHistory),
            'national' => $mailchimpCampaignReportRepository->findNationalReportRatio($scope->getMainCode(), $maxHistory),
            'zones' => array_map(function (Zone $zone): string {
                return $zone->getName();
            }, $scope->getZones()),
            'since' => new \DateTime("-$maxHistory days"),
        ];
    }
}
