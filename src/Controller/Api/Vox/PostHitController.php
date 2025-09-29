<?php

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\JeMengage\Hit\Command\SaveAppHitCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/v3/hit', name: 'api_post_hit', methods: ['POST'])]
class PostHitController extends AbstractController
{
    public function __invoke(Request $request, #[CurrentUser] Adherent $user, MessageBusInterface $bus): Response
    {
        $bus->dispatch(new SaveAppHitCommand($user->getId(), $user->currentAppSession?->getId(), $request->toArray()));

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}
