<?php

namespace App\Controller\Renaissance;

use App\Repository\CommitteeRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommitteesMapController extends AbstractController
{
    #[Route(path: '/carte-des-comites', name: 'app_committees_map', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('renaissance/committees_map.html.twig');
    }

    #[Route(path: '/committees-perimeters.json', name: 'app_committees_perimeters', methods: ['GET'])]
    public function getCommitteesPerimeters(CommitteeRepository $repository, CacheItemPoolInterface $cache): Response
    {
        $item = $cache->getItem('committees_map');

        if (!$item->isHit()) {
            $committees = $repository->getCommitteesPerimeters();
            $geoJson = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            foreach ($committees as $committee) {
                $geoJson['features'][] = [
                    'type' => 'Feature',
                    'properties' => [
                        'color' => \sprintf('#%06X', random_int(0, 0xFFFFFF)),
                        'name' => $committee['name'],
                        'id' => $committee['id'],
                    ],
                    'geometry' => json_decode($committee['features'], true),
                ];
            }

            $item->set(base64_encode($responseJson = json_encode($geoJson)));
            $item->expiresAt(new \DateTime('+10hours'));
            $cache->save($item);
        }

        $response = new Response($responseJson ?? base64_decode($item->get()));
        $response->setPublic();

        return $response;
    }
}
