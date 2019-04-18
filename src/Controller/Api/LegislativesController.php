<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

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
