<?php

namespace AppBundle\Controller\Api;

use AppBundle\Exception\EventException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/api")
 */
class CommitteesController extends Controller
{
    /**
     * @Route("/committees", defaults={"_enable_campaign_silence"=true}, name="api_committees")
     * @Method("GET")
     */
    public function getApprovedCommitteesAction()
    {
        return new JsonResponse($this->get('app.api.committee_provider')->getApprovedCommittees());
    }

    /**
     * @Route("/events", defaults={"_enable_campaign_silence"=true}, name="api_committees_events")
     * @Method("GET")
     */
    public function getUpcomingCommitteesEventsAction(Request $request)
    {
        try {
            return new JsonResponse($this->get('app.api.event_provider')->getUpcomingEvents($request->query->get('type')));
        } catch (EventException $e) {
            throw new BadRequestHttpException('Invalid HTTP request to fetch upcoming events. Some parameters may be invalid.', $e);
        }
    }
}
