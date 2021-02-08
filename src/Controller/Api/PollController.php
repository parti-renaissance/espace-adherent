<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Vote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PollController extends AbstractController
{
    /**
     * @Route(
     *     "/polls/{uuid}/vote",
     *     name="api_polls_vote",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"POST"}
     * )
     */
    public function vote(
        EntityManagerInterface $entityManager,
        Choice $choice,
        SerializerInterface $serializer
    ): JsonResponse {
        /* @var Adherent|null $user */
        $user = $this->getUser();

        $vote = new Vote($choice, $user);

        $entityManager->persist($vote);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($choice->getPoll(), 'json', ['groups' => ['poll_read']]),
            JsonResponse::HTTP_CREATED,
            [],
            true
        );
    }
}
