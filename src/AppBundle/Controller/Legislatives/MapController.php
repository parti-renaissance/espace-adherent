<?php

namespace AppBundle\Controller\Legislatives;

use AppBundle\Controller\Traits\CanaryControllerTrait;
use AppBundle\Entity\LegislativeCandidate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MapController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/la-carte", name="legislatives_map")
     * @Method("GET")
     */
    public function mapAction(): Response
    {
        $this->disableInProduction();

        return $this->render('legislatives/map.html.twig', [
            'candidates' => $this->getDoctrine()->getRepository(LegislativeCandidate::class)->findAll(), // @todo
        ]);
    }
}
