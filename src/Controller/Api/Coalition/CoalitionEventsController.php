<?php

namespace App\Controller\Api\Coalition;

use App\Entity\Coalition\Coalition;
use App\Repository\Event\BaseEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CoalitionEventsController extends AbstractController
{
    /**
     * @Route("/coalitions/{uuid}/events", name="api_coalition_events", methods={"GET"})
     */
    public function _invoke(
        Request $request,
        Coalition $coalition,
        BaseEventRepository $baseEventRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $events = $baseEventRepository->findEventsForCoalition(
            $coalition,
            $request->query->getInt('page', 1),
            $request->query->getInt('page_size', 30)
        );

        return $this->json(
            $events,
            Response::HTTP_OK,
            [],
            ['groups' => ['event_read', 'image_owner_exposed']]
        );
    }
}
