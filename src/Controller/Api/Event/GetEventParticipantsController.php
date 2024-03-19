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
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v3/events/{uuid}/participants.{_format}', name: 'api_events_get_participants', requirements: ['uuid' => '%pattern_uuid%', '_format' => 'json|xlsx'], defaults: ['_format' => 'json'], methods: ['GET'])]
#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'events')")]
class GetEventParticipantsController extends AbstractController
{
    public function __invoke(
        Request $request,
        BaseEvent $event,
        string $_format,
        EventRegistrationRepository $eventRegistrationRepository,
        EventParticipantsExporter $exporter
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
