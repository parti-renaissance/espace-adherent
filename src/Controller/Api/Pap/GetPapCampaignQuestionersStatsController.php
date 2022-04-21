<?php

namespace App\Controller\Api\Pap;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Pap\Campaign;
use App\Repository\AdherentRepository;
use App\Security\Voter\ScopeVisibilityVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetPapCampaignQuestionersStatsController extends AbstractController
{
    public function __invoke(
        Request $request,
        Campaign $campaign,
        AdherentRepository $adherentRepository
    ): PaginatorInterface {
        $this->denyAccessUnlessGranted(ScopeVisibilityVoter::PERMISSION, $campaign);

        return $adherentRepository->findFullScoresByPapCampaign(
            $campaign,
            $request->query->getInt('page', 1),
            $request->query->getInt('page_size', 100)
        );
    }
}
