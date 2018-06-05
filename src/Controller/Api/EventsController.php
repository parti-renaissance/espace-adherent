<?php

namespace AppBundle\Controller\Api;

use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\EventRegistrationRepository;
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
    public function eventsCountInReferentManagedAreaAction(Request $request, EventRepository $eventRepository, EventRegistrationRepository $eventRegistrationRepository, CommitteeRepository $committeeRepository): Response
    {
        $referent = $this->getUser();
        try {
            $filter = StatisticsParametersFilter::createFromRequest($request, $committeeRepository);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse([
            'events' => $eventRepository->countCommitteeEventsInReferentManagedArea($referent, $filter),
            'event_participants' => $eventRegistrationRepository->countEventParticipantsInReferentManagedArea($this->getUser(), $filter),
        ]);
    }

    /**
     * @Route("/count", name="app_events_count")
     * @Method("GET")
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function allTypesEventsCountInReferentManagedAreaAction(EventRepository $eventRepository, CitizenActionRepository $citizenActionRepository): Response
    {
        $referent = $this->getUser();

        return new JsonResponse([
            'current_total' => $eventRepository->countTotalEventsInReferentManagedAreaForCurrentMonth($referent),
            'events' => $eventRepository->countCommitteeEventsInReferentManagedArea($referent),
            'referent_events' => $eventRepository->countReferentEventsInReferentManagedArea($referent),
        ]);
    }

    /**
     * @Route("/count-participants", name="app_committee_events_count_participants")
     * @Method("GET")
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function eventsCountInReferentManagedArea(EventRepository $eventRepository, EventRegistrationRepository $eventRegistrationRepository): Response
    {
        $referent = $this->getUser();

        return new JsonResponse([
            'total' => $eventRepository->countParticipantsInReferentManagedArea($referent),
            'event_participants' => $eventRegistrationRepository->countEventParticipantsInReferentManagedArea($referent),
            'in_at_least_one_committee' => $eventRegistrationRepository->countEventParticipantsInReferentManagedAreaInAtLeastOneCommittee($referent),
        ]);
    }
}
