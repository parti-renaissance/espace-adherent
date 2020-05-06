<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LegislativesController extends Controller
{
    /**
     * @Route("/candidates", name="api_legislatives_candidates", methods={"GET"})
     */
    public function getCandidatesListAction(): JsonResponse
    {
        return new JsonResponse($this->get('app.api.legislative_candidate_provider')->getForApi());
    }
}
