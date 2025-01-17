<?php

namespace App\Controller\Api\Event;

use App\Entity\Event\BaseEvent;
use App\Exporter\EventParticipantsExporter;
use App\Normalizer\TranslateAdherentTagNormalizer;
use App\Repository\EventRegistrationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/events/{uuid}/participants.{_format}', name: 'api_events_get_participants', requirements: ['uuid' => '%pattern_uuid%', '_format' => 'json|xlsx'], defaults: ['_format' => 'json'], methods: ['GET'])]
#[Security("is_granted('REQUEST_SCOPE_GRANTED') and is_granted('IS_FEATURE_GRANTED', 'events') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', event)")]
class GetEventParticipantsController extends AbstractController
{
    public function __invoke(
        Request $request,
        BaseEvent $event,
        string $_format,
        EventRegistrationRepository $eventRegistrationRepository,
        EventParticipantsExporter $exporter,
    ): Response {
        if ('json' !== $_format) {
            return $exporter->export($event, $_format);
        }

        return $this->json(
            $eventRegistrationRepository->findPaginatedByEvent(
                $event,
                $request->query->getInt('page', 1),
                $request->query->getInt('page_size', 30)
            ),
            context: [TranslateAdherentTagNormalizer::ENABLE_TAG_TRANSLATOR => true]
        );
    }
}
