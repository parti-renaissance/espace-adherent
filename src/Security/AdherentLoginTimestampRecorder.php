<?php

namespace AppBundle\Security;

use AppBundle\Entity\Adherent;
use Doctrine\Common\Persistence\ObjectManager;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
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
        if (!$token instanceof UsernamePasswordToken && !$token instanceof OAuthToken) {
            throw new \RuntimeException(sprintf('Authentication token must be a instance of %s or %s.', UsernamePasswordToken::class, OAuthToken::class));
        }

        $user = $token->getUser();
        if ($user instanceof Adherent) {
            $user->recordLastLoginTime();
            $this->manager->flush();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }
}
