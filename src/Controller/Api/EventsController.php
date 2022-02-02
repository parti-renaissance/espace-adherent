<?php

namespace App\Controller\Api;

use App\Api\EventProvider;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Exporter\EventRegistrationExporter;
use App\Repository\CommitteeRepository;
use App\Repository\Event\BaseEventRepository;
use App\Repository\EventRegistrationRepository;
use App\Repository\EventRepository;
use App\Statistics\StatisticsParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class EventsController extends AbstractController
{
    /**
     * @Route("/upcoming-events", name="api_committees_events", methods={"GET"})
     */
    public function getUpcomingCommitteesEventsAction(Request $request, EventProvider $provider): Response
    {
        return new JsonResponse($provider->getUpcomingEvents(
            $request->query->getInt('type'),
            $this->getUser() instanceof Adherent
        ));
    }

    /**
     * @Route("/statistics/events/count-by-month", name="app_committee_events_count_by_month", methods={"GET"})
     * @Entity("referent", expr="repository.findReferent(referent)", converter="querystring")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function eventsCountInReferentManagedAreaAction(
        Request $request,
        Adherent $referent,
        EventRepository $eventRepository,
        EventRegistrationRepository $eventRegistrationRepository,
        CommitteeRepository $committeeRepository
    ): Response {
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

    /**
     * @Route("/statistics/events/count", name="app_events_count", methods={"GET"})
     * @Entity("referent", expr="repository.findReferent(referent)", converter="querystring")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function allTypesEventsCountInReferentManagedAreaAction(
        Adherent $referent,
        EventRepository $eventRepository
    ): Response {
        return new JsonResponse([
            'current_total' => $eventRepository->countTotalEventsInReferentManagedAreaForCurrentMonth($referent),
            'events' => $eventRepository->countCommitteeEventsInReferentManagedArea($referent),
            'referent_events' => $eventRepository->countReferentEventsInReferentManagedArea($referent),
        ]);
    }

    /**
     * @Route("/statistics/events/count-participants", name="app_committee_events_count_participants", methods={"GET"})
     * @Entity("referent", expr="repository.findReferent(referent)", converter="querystring")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function eventsCountInReferentManagedArea(
        Adherent $referent,
        EventRepository $eventRepository,
        EventRegistrationRepository $eventRegistrationRepository
    ): Response {
        return new JsonResponse([
            'total' => $eventRepository->countParticipantsInReferentManagedArea($referent),
            'participants' => $eventRegistrationRepository->countEventParticipantsInReferentManagedArea($referent),
            'participants_as_adherent' => $eventRegistrationRepository->countEventParticipantsAsAdherentInReferentManagedArea($referent),
        ]);
    }

    /**
     * @Route("/v3/events/registered", name="api_events_registered", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function followed(Request $request, UserInterface $user, BaseEventRepository $eventRepository): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $uuids = $body['uuids'] ?? null;

        if (!\is_array($uuids) || empty($uuids)) {
            return $this->json(['detail' => 'Parameter "uuids" should be an array of uuids.'], Response::HTTP_BAD_REQUEST);
        }

        $events = $eventRepository->findWithRegistrationByUuids($uuids, $user);

        return JsonResponse::create(array_map(function (BaseEvent $event) {
            return $event->getUuid();
        }, $events));
    }

    /**
     * @Route("/v3/events/{uuid}/export-registrations", name="api_export_event_registrations", requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     * @Security("event.getAuthor() === user")
     */
    public function exportRegistrations(EventRegistrationExporter $exporter, BaseEvent $event): Response
    {
        return $exporter->getResponse('xls', $event);
    }
}
