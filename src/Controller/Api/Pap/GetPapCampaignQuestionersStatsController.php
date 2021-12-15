<?php

namespace App\Controller\Api\Pap;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Pap\Campaign;
use App\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Request;

class GetPapCampaignQuestionersStatsController
{
    public function __invoke(
        Request $request,
        Campaign $campaign,
        AdherentRepository $adherentRepository
    ): PaginatorInterface {
        return $adherentRepository->findFullScoresByPapCampaign(
            $campaign,
            $request->query->get('page', 1),
            $request->query->get('page_size', 100)
        );
    }
}
