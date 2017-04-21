<?php

namespace AppBundle\Controller\Legislatives;

use AppBundle\Controller\Traits\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        return $this->render('legislatives/map.html.twig');
    }

    /**
     * @Route("/api/candidates", name="api_legislatives_candidates", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     */
    public function getCandidatesListAction(): JsonResponse
    {
        $this->disableInProduction();

        return new JsonResponse($this->get('app.api.legislative_candidate_provider')->getForApi());
    }
}
