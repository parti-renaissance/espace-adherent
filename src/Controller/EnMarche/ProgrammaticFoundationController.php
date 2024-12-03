<?php

namespace App\Controller\EnMarche;

use App\Entity\ProgrammaticFoundation\Measure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/projets-qui-marchent')]
class ProgrammaticFoundationController extends AbstractController
{
    #[Route(name: 'app_approaches', methods: ['GET'])]
    public function approachesAction(): Response
    {
        return $this->render('programmatic_foundation/approaches.html.twig');
    }

    #[Route(path: '/mesures/{uuid}', name: 'app_approach_measure_view', methods: ['GET'])]
    public function viewMeasureAction(Measure $measure): Response
    {
        return $this->render('programmatic_foundation/view_measure.html.twig', [
            'measure' => $measure,
        ]);
    }
}
