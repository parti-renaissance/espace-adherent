<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\Poll\Vote;
use App\Poll\PollDataBuilder;
use App\Poll\Request\CreatePollVoteRequest;
use App\Repository\Poll\ChoiceRepository;
use App\Repository\Poll\PollRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PollController extends AbstractController
{
    #[Route(path: '/v3/polls/vote', name: 'api_polls_vote', methods: ['POST'])]
    public function vote(
        #[MapRequestPayload]
        CreatePollVoteRequest $payload,
        #[CurrentUser]
        Adherent $user,
        EntityManagerInterface $entityManager,
        ChoiceRepository $choiceRepository,
        PollDataBuilder $dataBuilder,
    ): JsonResponse {
        $choice = $choiceRepository->findOneByUuid($payload->uuid);

        if (!$choice) {
            throw new NotFoundHttpException("Choice with uuid '{$payload->uuid}' does not exist.");
        }

        $now = new \DateTimeImmutable();
        $poll = $choice->getPoll();
        if (!$poll->isVotePeriodActive($now)) {
            return $this->json(['message' => 'Poll is not open.'], Response::HTTP_CONFLICT);
        }

        $entityManager->persist(new Vote($choice, $user));
        $entityManager->flush();

        return $this->json($dataBuilder->build($poll, $now, $user), Response::HTTP_CREATED);
    }

    #[Route(path: '/v3/polls', name: 'api_poll', methods: ['GET'])]
    public function getCurrentPoll(
        #[CurrentUser]
        ?Adherent $user,
        PollRepository $pollRepository,
        PollDataBuilder $dataBuilder,
    ): JsonResponse {
        $poll = $pollRepository->findLastActivePoll();

        if (null === $poll) {
            return $this->json(null);
        }

        return $this->json($dataBuilder->build($poll, new \DateTimeImmutable(), $user));
    }
}
