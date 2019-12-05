<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\ProgrammaticFoundation\Measure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class ProgrammaticFoundationController extends Controller
{
    /**
     * @Route("/socle-programme", name="app_approaches", methods={"GET"})
     */
    public function approachesAction(): Response
    {
        return $this->render('programmatic_foundation/approaches.html.twig');
    }

    /**
     * @Route("/socle-programme/mesures/{uuid}", name="app_approach_measure_view", methods={"GET"})
     */
    public function viewMeasureAction(Measure $measure): Response
    {
        return $this->render('programmatic_foundation/view_measure.html.twig', [
            'measure' => $measure,
        ]);
    }
}
