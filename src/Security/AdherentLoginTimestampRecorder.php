<?php

namespace App\Security;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AdherentLoginTimestampRecorder implements EventSubscriberInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();

        $user = $token->getUser();
        // Only record adherent logins
        if (!$user instanceof Adherent) {
            return;
        }

        // OAuth calls are not login attempts
        if ($token instanceof PostAuthenticationGuardToken && 'api_oauth' === $token->getProviderKey()) {
            return;
        }

        $user->recordLastLoginTime();
        $this->manager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }
}
