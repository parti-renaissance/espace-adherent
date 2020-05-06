<?php

namespace App\Security;

use App\Entity\Adherent;
use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
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

        if (!$token instanceof PostAuthenticationGuardToken
            && !$token instanceof JWTUserToken
            && !$token instanceof UsernamePasswordToken
        ) {
            throw new \RuntimeException(sprintf('Authentication token must be a %s or %s or %s instance. (%s given)', PostAuthenticationGuardToken::class, JWTUserToken::class, UsernamePasswordToken::class, \get_class($token)));
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
