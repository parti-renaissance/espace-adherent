<?php

namespace App\EventListener;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class FinishAdhesionListener implements EventSubscriberInterface
{
    public function __construct(private readonly Security $security, private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [RequestEvent::class => 'onRequestEvent'];
    }

    public function onRequestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (
            !$event->isMainRequest()
            || !str_starts_with($request->getPathInfo(), '/espace-adherent')
        ) {
            return;
        }

        $adherent = $this->security->getUser();

        if (!$adherent instanceof Adherent) {
            return;
        }

        if (!$adherent->isV2() || $adherent->isFullyCompletedAdhesion()) {
            return;
        }

        if ($nextStepRouteName = AdhesionStepEnum::getNextStep($adherent->getFinishedAdhesionSteps())) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate($nextStepRouteName)));
        }
    }
}
