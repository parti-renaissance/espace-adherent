<?php

namespace App\Controller\Admin;

use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminStatsController extends AbstractController
{
    #[Route('/stats/adhesion-par-departement', name: 'admin_app_stats_adhesion_per_department', methods: ['GET'])]
    public function listAction(Request $request, AdherentRepository $adherentRepository): Response
    {
        return $this->render('admin/stats/adhesion_per_department.html.twig', [
            'stats_per_department' => $adherentRepository->getStatsPerDepartment(),
        ]);
    }
}
