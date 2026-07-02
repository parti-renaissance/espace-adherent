<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox\Poll;

use App\Entity\Adherent;
use App\Entity\Poll\Poll;
use App\Normalizer\PollNormalizer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/polls/{uuid}', name: 'api_v3_poll_get', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class GetPollController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['uuid' => 'uuid'])]
        Poll $poll,
        #[CurrentUser]
        ?Adherent $user,
    ): JsonResponse {
        return $this->json($poll, context: [PollNormalizer::CONTEXT_ADHERENT => $user]);
    }
}
