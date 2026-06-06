<?php

declare(strict_types=1);

namespace App\Adhesion\Listener;

use App\Adhesion\AdhesionStepEnum;
use App\AppCodeEnum;
use App\Entity\Adherent;
use App\OAuth\App\AuthAppUrlManager;
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
        private readonly AuthAppUrlManager $appUrlManager,
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

        // stop temporarily redirect to adhesion steps
        return;

        if (AppCodeEnum::CAMPAIGN === $this->appUrlManager->getAppCodeFromRequest($request)) {
            return;
        }

        $adherent = $this->security->getUser();

        if (
            !$adherent instanceof Adherent
            || $adherent->signupAccount
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
