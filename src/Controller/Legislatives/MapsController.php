<?php

namespace AppBundle\Controller\Legislatives;

use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MapsController extends Controller
{
    /**
     * @Route("/la-carte", defaults={"_enable_campaign_silence"=true}, name="legislatives_map")
     * @Method("GET")
     */
    public function candidatesAction(): Response
    {
        return $this->render('legislatives/map_candidates.html.twig');
    }

    /**
     * @Route("/les-evenements", defaults={"_enable_campaign_silence"=true}, name="legislatives_events")
     * @Method("GET")
     */
    public function eventsAction(): Response
    {
        return $this->render('legislatives/map_events.html.twig', [
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->countUpcomingEvents(),
        ]);
    }
}
