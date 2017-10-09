<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class StatsController extends Controller
{
    /**
     * @Route("/stats", defaults={"_enable_campaign_silence"=true}, name="api_stats")
     * @Method("GET")
     */
    public function indexAction()
    {
        return new JsonResponse([
            'userCount' => $this->getDoctrine()->getRepository(Adherent::class)->countElements(),
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->countElements(),
            'committeeCount' => $this->getDoctrine()->getRepository(Committee::class)->countElements(),
        ]);
    }
}
