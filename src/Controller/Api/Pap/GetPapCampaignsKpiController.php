<?php

namespace App\Controller\Api\Pap;

use App\Repository\Pap\CampaignRepository;

class GetPapCampaignsKpiController
{
    public function __invoke(CampaignRepository $campaignRepository): array
    {
        return $campaignRepository->findCampaignsKpi();
    }
}
