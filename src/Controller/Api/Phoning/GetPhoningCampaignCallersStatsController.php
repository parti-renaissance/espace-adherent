<?php

declare(strict_types=1);

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\Campaign;
use App\Repository\AdherentRepository;

class GetPhoningCampaignCallersStatsController
{
    public function __invoke(Campaign $campaign, AdherentRepository $adherentRepository): array
    {
        return $adherentRepository->findFullScoresByCampaign($campaign, true);
    }
}
