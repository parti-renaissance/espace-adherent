<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends Controller
{
    /**
     * @Route("/events", defaults={"_enable_campaign_silence"=true}, name="api_committees_events")
     * @Method("GET")
     */
    public function getUpcomingCommitteesEventsAction(Request $request)
    {
        return new JsonResponse($this->get('app.api.event_provider')->getUpcomingEvents($request->query->getInt('type')));
    }
}
