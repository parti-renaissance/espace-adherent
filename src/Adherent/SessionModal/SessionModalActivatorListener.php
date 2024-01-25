<?php

namespace App\Adherent\SessionModal;

use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SessionModalActivatorListener implements EventSubscriberInterface
{
    public const DISMISS_COOKIE_KEY = 'session_modal_dismiss';
    public const SESSION_KEY = 'session_modal';

    public const CONTEXT_CERTIFICATION = 'certification';

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $token = $event->getAuthenticationToken();

        if ($token instanceof PostAuthenticationGuardToken && 'api_oauth' === $token->getProviderKey()) {
            return;
        }

        $adherent = $token->getUser();

        if (!$adherent instanceof Adherent) {
            return;
        }

        $request = $event->getRequest();

        if ($request->query->has('_target_path') || $request->request->has('_target_path')) {
            return;
        }

        if ($request->cookies->has(self::DISMISS_COOKIE_KEY)) {
            $request->getSession()->remove(self::SESSION_KEY);

            return;
        }

        if ($adherent->getLastMembershipDonation() < new \DateTime((date('Y') - 1).'-01-01 00:00:00')) {
            $request->getSession()->set(self::SESSION_KEY, self::CONTEXT_CERTIFICATION);
        }
    }
}
