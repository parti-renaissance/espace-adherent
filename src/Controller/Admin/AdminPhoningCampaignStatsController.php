<?php

namespace App\Controller\Admin;

use App\Controller\EnMarche\VotingPlatform\AbstractController;
use App\Entity\Phoning\Campaign;
use App\Repository\AdherentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/phoning-campaign/{id}/stats", name="app_admin_phoning_campaign_stats", methods="GET")
 * @Security("is_granted('ROLE_ADMIN_PHONING_CAMPAIGNS')")
 */
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
