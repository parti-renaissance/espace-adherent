<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Committee;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Statistics\StatisticsParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/events")
 */
class EventsController extends Controller
{
    /**
     * @Route("", name="api_committees_events")
     * @Method("GET")
     */
    public function getUpcomingCommitteesEventsAction(Request $request): Response
    {
        return new JsonResponse($this->get('app.api.event_provider')->getUpcomingEvents($request->query->getInt('type')));
    }

    /**
     * @Route("/count-by-month", name="app_committee_events_count_by_month")
     * @Method("GET")
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function eventsCountInReferentManagedAreaAction(Request $request, EventRepository $eventRepository): Response
    {
        $referent = $this->getUser();
        try {
            $filter = StatisticsParametersFilter::createFromRequest($request, $this->getDoctrine()->getRepository(Committee::class));
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $events = $eventRepository->countCommitteeEventsInReferentManagedArea($referent, $filter);

        return new JsonResponse($events);
    }

    /**
     * @Route("/count", name="app_events_count")
     * @Method("GET")
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function allTypesEventsCountInReferentManagedAreaAction(EventRepository $eventRepository, CitizenActionRepository $citizenActionRepository): Response
    {
        $referent = $this->getUser();
        $events = $eventRepository->countCommitteeEventsInReferentManagedArea($referent);
        $referentEvents = $eventRepository->countReferentEventsInReferentManagedArea($referent);

        return new JsonResponse([
            'monthly' => array_merge_recursive($events, $referentEvents),
        ]);
    }
}
