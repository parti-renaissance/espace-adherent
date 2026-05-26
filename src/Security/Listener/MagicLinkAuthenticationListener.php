<?php

declare(strict_types=1);

namespace App\Security\Listener;

use App\Adhesion\AdhesionStepEnum;
use App\Controller\Renaissance\MagicLinkController;
use App\Entity\Adherent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MagicLinkAuthenticationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [AuthenticationSuccessEvent::class => ['onAuthenticationSuccess', 4096]];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $request = $this->requestStack->getMainRequest();

        if (MagicLinkController::ROUTE_NAME !== $request?->attributes->get('_route')) {
            return;
        }

        $adherent = $event->getAuthenticationToken()->getUser();

        if (!$adherent instanceof Adherent) {
            return;
        }

        $justValidated = false;
        if ($adherent->isPending()) {
            $adherent->enable();
            $justValidated = true;
        }

        if (!$adherent->hasFinishedAdhesionStep(AdhesionStepEnum::ACTIVATION)) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::ACTIVATION);
        }

        $this->entityManager->flush();

        if ($justValidated) {
            $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);
        }
    }
}
