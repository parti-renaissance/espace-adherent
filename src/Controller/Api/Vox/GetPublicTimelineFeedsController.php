<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\JeMengage\Timeline\PublicFeed\PublicTimelineProvider;
use App\Repository\Geo\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route(path: '/timeline-feeds', name: 'api_public_timeline_feeds', methods: ['GET'])]
class GetPublicTimelineFeedsController extends AbstractController
{
    public function __invoke(Request $request, PublicTimelineProvider $provider, ZoneRepository $zoneRepository): JsonResponse
    {
        $page = max(0, $request->query->getInt('page'));

        $zone = null;
        if (($zoneUuid = $request->query->get('zone')) && Uuid::isValid($zoneUuid)) {
            $zone = $zoneRepository->findOneByUuid($zoneUuid);
        }

        $response = $this->json($provider->findItems($page, $zone));

        $response->setPublic();
        $response->setMaxAge(120);

        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

        return $response;
    }
}
