<?php

namespace App\Controller\Renaissance;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\LiveStream;
use App\Event\EventVisibilityEnum;
use App\History\UserActionHistoryHandler;
use App\OAuth\OAuthAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LiveStreamController extends AbstractController
{
    public function __construct(private readonly OAuthAuthenticator $authAuthenticator)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/live-stream/{uuid}', name: 'app_live_stream', methods: ['GET'])]
    public function liveStreamAction(LiveStream $liveStream, UserInterface $user): Response
    {
        /** @var Adherent $user */
        if (!$user->isRenaissanceAdherent()) {
            $this->addFlash('info', 'Vous devez être adhérent pour accéder au live stream.');

            return $this->redirectToRoute('app_adhesion_index');
        }

        return $this->render('renaissance/live_stream.html.twig', [
            'live_stream' => $liveStream,
        ]);
    }

    #[Route('/live-event/{slug}', name: 'app_live_event', methods: ['GET'])]
    public function liveEventAction(Request $request, UserActionHistoryHandler $userActionHistoryHandler, Event $event): Response
    {
        if (
            !$event->isPublished()
            || !$event->isActive()
            || !$event->isLivePlayerEnabled()
        ) {
            throw $this->createNotFoundException();
        }

        try {
            $adherent = $this->getAdherent($request);
        } catch (AuthenticationException $e) {
            if (!$event->isPublic()) {
                throw $e;
            }
            $adherent = null;
        }

        if (
            $event->isForAdherent()
            && (
                !$adherent
                || (EventVisibilityEnum::ADHERENT === $event->visibility && !$adherent->hasTag(TagEnum::ADHERENT))
                || (EventVisibilityEnum::ADHERENT_DUES === $event->visibility && !$adherent->hasTag(TagEnum::getAdherentYearTag()))
            )
        ) {
            if (!$adherent || (EventVisibilityEnum::ADHERENT === $event->visibility && !$adherent->hasTag(TagEnum::ADHERENT))) {
                $this->addFlash('info', 'Vous devez être adhérent pour accéder à cet événement.');
            } elseif (EventVisibilityEnum::ADHERENT_DUES === $event->visibility && !$adherent->hasTag(TagEnum::getAdherentYearTag())) {
                $this->addFlash('info', 'Vous devez être à jour de cotisation pour accéder à cet événement.');
            }

            return $this->redirectToRoute('app_adhesion_index');
        }

        if ($adherent) {
            $userActionHistoryHandler->createLiveParticipation($adherent, $event);
        }

        return $this->render('renaissance/live_event.html.twig', [
            'event' => $event,
        ]);
    }

    protected function getAdherent(Request $request): ?Adherent
    {
        if (($adherent = $this->getUser()) instanceof Adherent) {
            return $adherent;
        }

        $newRequest = $request->duplicate([]);
        $newRequest->headers->set('Authorization', 'Bearer '.$request->query->get('token'));

        return $this->authAuthenticator->authenticate($newRequest)->getUser();
    }
}
