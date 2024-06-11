<?php

namespace App\Controller\Admin;

use App\Repository\Geo\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminProcurationStatsController extends AbstractController
{
    #[Route('/stats/procurations-par-departement', name: 'admin_app_stats_procuration_per_department', methods: ['GET'])]
    public function listAction(ZoneRepository $zoneRepository): Response
    {
        $zones = $zoneRepository->getAllForProcurationsStats();

        return $this->render('admin/stats/procuration_per_department.html.twig', [
            'stats_per_department' => $zones,
        ]);
    }

    private function getStats(array $zones): array
    {
        // Initialize district referent tags for adherents
        $sql = <<<SQL
            SELECT
                zone.code,
                zone.name,
            FROM geo_zone AS zone
            LEFT JOIN procuration_v2_requests AS pr
                ON
            WHERE zone.type IN (:zone_types)
            SQL;

        $manager->getConnection()->exec($sql);
    }
}
