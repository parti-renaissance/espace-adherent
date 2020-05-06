<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends Controller
{
    /**
     * @Route("/stats", name="api_stats", methods={"GET"})
     */
    public function indexAction()
    {
        return new JsonResponse([
            'userCount' => $this->getDoctrine()->getRepository(Adherent::class)->countAdherents(),
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->countElements(),
            'committeeCount' => $this->getDoctrine()->getRepository(Committee::class)->countElements(),
        ]);
    }
}
