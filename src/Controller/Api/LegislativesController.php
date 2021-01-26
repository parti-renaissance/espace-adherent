<?php

namespace App\Controller\Api;

use App\Api\LegislativeCandidateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LegislativesController extends AbstractController
{
    /**
     * @Route("/candidates", name="api_legislatives_candidates", methods={"GET"})
     */
    public function getCandidatesListAction(LegislativeCandidateProvider $provider): JsonResponse
    {
        return new JsonResponse($provider->getForApi());
    }
}
