<?php

namespace App\Controller\Api\Phoning;

use App\Repository\Phoning\CampaignRepository;

class GetPhoningCampaignsKpiController
{
    public function __invoke(CampaignRepository $campaignRepository): array
    {
        return $campaignRepository->findPhoningCampaignsKpi();
    }
}
