<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api")
 */
class CommitteesController extends Controller
{
    /**
     * @Route("/committees", name="api_committees")
     * @Method("GET")
     */
    public function getApprovedCommitteesAction()
    {
        return new JsonResponse($this->get('app.api.committee_provider')->getApprovedCommittees());
    }

    /**
     * @Route("/events", name="api_committees_events")
     * @Method("GET")
     */
    public function getUpcomingCommitteesEventsAction()
    {
        return new JsonResponse($this->get('app.api.event_provider')->getUpcomingEvents());
    }
}
