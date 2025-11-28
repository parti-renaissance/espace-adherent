<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\AdherentRepository;
use App\Repository\Geo\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminStatsController extends AbstractController
{
    #[Route('/stats/adhesions-par-departement', name: 'admin_app_stats_adhesion_per_department', methods: ['GET'])]
    public function listAction(AdherentRepository $adherentRepository, ZoneRepository $zoneRepository): Response
    {
        return $this->render('admin/stats/adhesion_per_department.html.twig', [
            'stats_per_department' => $adherentRepository->getStatsPerZones($zoneRepository->getAllForAdherentsStats()),
        ]);
    }
}
