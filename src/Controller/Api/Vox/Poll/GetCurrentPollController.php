<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox\Poll;

use App\Entity\Adherent;
use App\Normalizer\PollNormalizer;
use App\Repository\Poll\PollRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/polls/current', name: 'api_v3_poll_current', methods: ['GET'])]
class GetCurrentPollController extends AbstractController
{
    public function __invoke(
        #[CurrentUser]
        Adherent $user,
        PollRepository $pollRepository,
    ): JsonResponse {
        $poll = $pollRepository->findLastActivePoll();

        if (null === $poll) {
            return $this->json(null);
        }

        return $this->json($poll, context: [PollNormalizer::CONTEXT_ADHERENT => $user]);
    }
}
