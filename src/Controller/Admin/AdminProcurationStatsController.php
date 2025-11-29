<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProcurationV2\Round;
use App\Procuration\V2\ProcurationStatsHandler;
use App\Repository\Procuration\ElectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminProcurationStatsController extends AbstractController
{
    #[Route('/stats/procurations', name: 'admin_app_stats_procuration_round_list', methods: ['GET'])]
    public function listRoundsAction(ElectionRepository $electionRepository): Response
    {
        return $this->render('admin/stats/procuration_round_list.html.twig', [
            'elections' => $electionRepository->findAllOrderedByRoundDates(),
        ]);
    }

    #[Route('/stats/procurations/{uuid}', name: 'admin_app_stats_procuration_round_stats', methods: ['GET'], requirements: ['uuid' => '%pattern_uuid%'])]
    public function statsRoundAction(Round $round, ProcurationStatsHandler $statsHandler): Response
    {
        return $this->render('admin/stats/procuration_round_stats.html.twig', [
            'round' => $round,
            'global_stats' => $statsHandler->getGlobalStats($round),
            'zones_stats' => $statsHandler->getZonesStats($round),
        ]);
    }
}
