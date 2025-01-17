<?php

namespace App\Controller\Api\Event;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/events/coordinates', name: 'api_events_get_coordinates', methods: ['GET'])]
class GetEventsCoordinatesController extends AbstractController
{
    public function __invoke(EventRepository $repository): Response
    {
        $events = $repository->findAllForPublicMap();

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
                    'date' => $event['beginAt']->format('\L\e d/m/Y à H:i'),
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
