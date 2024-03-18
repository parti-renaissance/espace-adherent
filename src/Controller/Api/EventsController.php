<?php

namespace App\Controller\Api;

use App\Api\EventProvider;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Exporter\EventRegistrationExporter;
use App\Repository\Event\BaseEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventsController extends AbstractController
{
    #[Route(path: '/upcoming-events', name: 'api_committees_events', methods: ['GET'])]
    public function getUpcomingCommitteesEventsAction(Request $request, EventProvider $provider): Response
    {
        return new JsonResponse($provider->getUpcomingEvents(
            $request->query->getInt('type'),
            $this->getUser() instanceof Adherent
        ));
    }

    #[Route(path: '/v3/events/registered', name: 'api_events_registered', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function followed(Request $request, BaseEventRepository $eventRepository): JsonResponse
    {
        /** @var Adherent $user */
        $user = $this->getUser();
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

    #[Route(path: '/v3/events/{uuid}/export-registrations', name: 'api_export_event_registrations', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
    #[Security('event.getAuthor() === user')]
    public function exportRegistrations(EventRegistrationExporter $exporter, BaseEvent $event): Response
    {
        return $exporter->getResponse('xlsx', $event);
    }
}
