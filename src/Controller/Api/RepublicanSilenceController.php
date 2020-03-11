<?php

namespace AppBundle\Controller\Api;

use AppBundle\RepublicanSilence\RepublicanSilenceManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/republican-silence/current")
 */
class RepublicanSilenceController extends AbstractController
{
    private $silenceManager;

    public function __construct(RepublicanSilenceManager $silenceManager)
    {
        $this->silenceManager = $silenceManager;
    }

    public function __invoke(): JsonResponse
    {
        return $this->json(
            $this->silenceManager->getRepublicanSilencesForDate(new \DateTime()),
            Response::HTTP_OK,
            [],
            ['groups' => ['read_api']]
        );
    }
}
