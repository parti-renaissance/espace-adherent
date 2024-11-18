<?php

namespace App\Security\Listener;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AdherentLoginTimestampRecorderListener implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        // Only record adherent logins
        if (!$user instanceof Adherent) {
            return;
        }

        $user->recordLastLoginTime();
        $this->manager->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onSecurityInteractiveLogin',
        ];
    }
}
