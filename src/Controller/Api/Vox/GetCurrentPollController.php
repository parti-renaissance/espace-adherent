<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Repository\Poll\PollRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCurrentPollController extends AbstractController
{
    #[Route('/v3/polls/current', name: 'api_v3_poll_current', methods: ['GET'])]
    public function __invoke(PollRepository $pollRepository): Response
    {
        $poll = $pollRepository->findLastActivePoll();

        if (null === $poll) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->json($poll, Response::HTTP_OK, [], [
            'groups' => ['poll_read'],
            PrivatePublicContextBuilder::CONTEXT_KEY => PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER,
        ]);
    }
}
