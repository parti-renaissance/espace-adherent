<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api")
 */
class StatsController extends Controller
{
    /**
     * @Route("/stats", defaults={"_enable_campaign_silence"=true}, name="api_stats")
     * @Method("GET")
     */
    public function indexAction()
    {
        return new JsonResponse([
            'userCount' => $this->getDoctrine()->getRepository(Adherent::class)->count(),
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->count(),
            'committeeCount' => $this->getDoctrine()->getRepository(Committee::class)->count(),
        ]);
    }
}
