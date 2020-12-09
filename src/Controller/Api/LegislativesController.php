<?php

namespace App\Controller\Api;

use App\Api\LegislativeCandidateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LegislativesController extends Controller
{
    /**
     * @Route("/candidates", name="api_legislatives_candidates", methods={"GET"})
     */
    public function getCandidatesListAction(LegislativeCandidateProvider $provider): JsonResponse
    {
        return new JsonResponse($provider->getForApi());
    }
}
