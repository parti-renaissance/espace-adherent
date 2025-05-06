<?php

namespace App\Adhesion\Listener;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FinishAdhesionStepsListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [RequestEvent::class => 'onRequestEvent'];
    }

    public function onRequestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !\in_array($request->attributes->get('_route'), ['vox_app_redirect', 'app_front_oauth_authorize'])) {
            return;
        }

        $adherent = $this->security->getUser();

        if (
            !$adherent instanceof Adherent
            || $adherent->isFullyCompletedAdhesion()
            || $this->security->isGranted('IS_IMPERSONATOR')
        ) {
            return;
        }

        if ($nextStepRouteName = AdhesionStepEnum::getNextStep($adherent->isRenaissanceAdherent(), $adherent->getFinishedAdhesionSteps())) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate($nextStepRouteName)));
        }
    }
}
