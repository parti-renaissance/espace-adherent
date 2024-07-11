<?php

namespace App\Controller\Api\Statistics;

use App\Repository\CommitteeRepository;
use App\Repository\EventRegistrationRepository;
use App\Repository\EventRepository;
use App\Statistics\StatisticsParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_OAUTH_SCOPE_READ:STATS')]
#[Route(path: '/statistics/events')]
class EventsController extends AbstractStatisticsController
{
    #[Route(path: '/count-by-month', name: 'app_statistics_committee_events_count_by_month', methods: ['GET'])]
    public function eventsCountInReferentManagedAreaAction(
        Request $request,
        EventRepository $eventRepository,
        EventRegistrationRepository $eventRegistrationRepository,
        CommitteeRepository $committeeRepository
    ): Response {
        $referent = $this->findReferent($request);

        try {
            $filter = StatisticsParametersFilter::createFromRequest($request, $committeeRepository);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse([
            'events' => $eventRepository->countCommitteeEventsInReferentManagedArea($referent, $filter),
            'event_participants' => $eventRegistrationRepository->countEventParticipantsInReferentManagedArea($referent, $filter),
        ]);
    }

    #[Route(path: '/count', name: 'app_statistics_events_count', methods: ['GET'])]
    public function allTypesEventsCountInReferentManagedAreaAction(
        Request $request,
        EventRepository $eventRepository
    ): Response {
        $referent = $this->findReferent($request);

        return new JsonResponse([
            'current_total' => $eventRepository->countTotalEventsInReferentManagedAreaForCurrentMonth($referent),
            'events' => $eventRepository->countCommitteeEventsInReferentManagedArea($referent),
            'referent_events' => $eventRepository->countReferentEventsInReferentManagedArea($referent),
        ]);
    }

    #[Route(path: '/count-participants', name: 'app_statistics_committee_events_count_participants', methods: ['GET'])]
    public function eventsCountInReferentManagedArea(
        Request $request,
        EventRepository $eventRepository,
        EventRegistrationRepository $eventRegistrationRepository
    ): Response {
        $referent = $this->findReferent($request);

        return new JsonResponse([
            'total' => $eventRepository->countParticipantsInReferentManagedArea($referent),
            'participants' => $eventRegistrationRepository->countEventParticipantsInReferentManagedArea($referent),
            'participants_as_adherent' => $eventRegistrationRepository->countEventParticipantsAsAdherentInReferentManagedArea($referent),
        ]);
    }
}
