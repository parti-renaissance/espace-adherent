<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends Controller
{
    /**
     * @Route("/stats", name="api_stats")
     * @Method("GET")
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
