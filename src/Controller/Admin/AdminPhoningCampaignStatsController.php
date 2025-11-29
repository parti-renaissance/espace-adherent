<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\EnMarche\VotingPlatform\AbstractController;
use App\Entity\Phoning\Campaign;
use App\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN_PHONING_CAMPAIGNS')]
#[Route(path: '/phoning-campaign/{id}/stats', name: 'app_admin_phoning_campaign_stats', methods: 'GET')]
class AdminPhoningCampaignStatsController extends AbstractController
{
    public function __invoke(AdherentRepository $adherentRepository, Campaign $campaign): Response
    {
        return $this->render('admin/phoning/campaign/stats.html.twig', [
            'campaign' => $campaign,
            'callers' => $adherentRepository->findFullScoresByCampaign($campaign),
        ]);
    }
}
