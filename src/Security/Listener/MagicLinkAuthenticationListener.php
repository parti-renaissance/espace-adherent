<?php

namespace App\Security\Listener;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\LoginLinkAuthenticator;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class MagicLinkAuthenticationListener implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => ['onAuthenticationSuccess', 4096]];
    }

    public function onAuthenticationSuccess(LoginSuccessEvent $event): void
    {
        if (!$event->getAuthenticator() instanceof LoginLinkAuthenticator) {
            return;
        }

        $adherent = $event->getUser();

        if (!$adherent instanceof Adherent || $adherent->getActivatedAt()) {
            return;
        }

        $adherent->enable();
        $adherent->finishAdhesionStep(AdhesionStepEnum::ACTIVATION);

        $this->entityManager->flush();
    }
}
