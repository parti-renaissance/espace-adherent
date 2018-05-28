<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class LegislativesController extends Controller
{
    /**
     * @Route("/candidates", name="api_legislatives_candidates")
     * @Method("GET")
     */
    public function getCandidatesListAction(): JsonResponse
    {
        return new JsonResponse($this->get('app.api.legislative_candidate_provider')->getForApi());
    }
}
