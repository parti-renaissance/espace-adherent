<?php

namespace App\Controller\Renaissance;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\LiveStream;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LiveStreamController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/live-stream/{uuid}', name: 'app_live_stream', methods: ['GET'])]
    public function liveStreamAction(LiveStream $liveStream, UserInterface $user): Response
    {
        /** @var Adherent $user */
        if (!$user->hasActiveMembership()) {
            $this->addFlash('info', 'Vous devez être adhérent pour accéder au live stream.');

            return $this->redirectToRoute('app_adhesion_index');
        }

        return $this->render('renaissance/live_stream.html.twig', [
            'live_stream' => $liveStream,
        ]);
    }

    #[Route('/live-event/{slug}', name: 'app_live_event', methods: ['GET'])]
    public function liveEventAction(Request $request, Event $event): Response
    {
        return $this->render('renaissance/live_event.html.twig', [
            'event' => $event,
        ]);
    }
}
