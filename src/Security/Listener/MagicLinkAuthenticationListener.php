<?php

namespace App\Security\Listener;

use App\Controller\Renaissance\MagicLinkController;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class MagicLinkAuthenticationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
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

        if (!$adherent instanceof Adherent || !$adherent->isPending()) {
            return;
        }

        $adherent->enable();

        $this->entityManager->flush();
    }
}
