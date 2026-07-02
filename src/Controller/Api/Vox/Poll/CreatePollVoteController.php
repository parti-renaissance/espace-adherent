<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox\Poll;

use App\Entity\Adherent;
use App\Entity\Poll\Poll;
use App\Entity\Poll\Vote;
use App\Normalizer\PollNormalizer;
use App\Poll\Request\CreatePollVoteRequest;
use App\Repository\Poll\ChoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/polls/{uuid}', name: 'api_v3_poll_vote', requirements: ['uuid' => '%pattern_uuid%'], methods: ['POST'])]
class CreatePollVoteController extends AbstractController
{
    public function __invoke(
        Poll $poll,
        #[MapRequestPayload]
        CreatePollVoteRequest $payload,
        #[CurrentUser]
        Adherent $user,
        EntityManagerInterface $entityManager,
        ChoiceRepository $choiceRepository,
    ): JsonResponse {
        $choice = $choiceRepository->findOneByUuid($payload->choice);

        if (!$choice || !$choice->getPoll()->equals($poll)) {
            throw new NotFoundHttpException("Choice with uuid '{$payload->choice}' does not exist.");
        }

        $now = new \DateTimeImmutable();
        if (!$poll->isVotePeriodActive($now)) {
            return $this->json(['message' => 'Poll is not open.'], Response::HTTP_CONFLICT);
        }

        $entityManager->persist(new Vote($choice, $user));
        $entityManager->flush();

        return $this->json($poll, Response::HTTP_CREATED, [], [
            PollNormalizer::CONTEXT_NOW => $now,
            PollNormalizer::CONTEXT_ADHERENT => $user,
        ]);
    }
}
