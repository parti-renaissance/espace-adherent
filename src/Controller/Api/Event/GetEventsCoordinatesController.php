<?php

declare(strict_types=1);

namespace App\Controller\Api\Event;

use App\Repository\Event\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/events/coordinates', name: 'api_events_get_coordinates', methods: ['GET'])]
class GetEventsCoordinatesController extends AbstractController
{
    public function __invoke(Request $request, EventRepository $repository): Response
    {
        $events = $repository->findAllForPublicMap($request->query->get('category'));

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($events as $event) {
            $feature = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $event['longitude'], (float) $event['latitude']],
                ],
                'properties' => [
                    'slug' => $event['slug'],
                    'name' => $event['name'],
                    'date' => $event['beginAt']->format('Y-m-d H:i:s'),
                    'timestamp' => $event['beginAt']->getTimestamp(),
                    'city' => $event['city'],
                    'country' => $event['country'],
                    'postalCode' => $event['postalCode'],
                ],
            ];

            $geojson['features'][] = $feature;
        }

        return $this->json($geojson);
    }
}
