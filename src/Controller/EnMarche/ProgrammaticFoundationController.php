<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\ProgrammaticFoundation\Measure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/socle-programme/mesures/{slug}", name="app_approach_measure_view", methods={"GET"})
     * @Entity("measure", expr="repository.findOneBySlug(slug)")
     */
    public function viewMeasureAction(Measure $measure): Response
    {
        return $this->render('programmatic_foundation/view_measure.html.twig', [
            'measure' => $measure,
        ]);
    }
}
