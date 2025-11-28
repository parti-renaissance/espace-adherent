<?php

declare(strict_types=1);

namespace App\Adherent\SessionModal;

use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SessionModalActivatorListener implements EventSubscriberInterface
{
    public const DISMISS_COOKIE_KEY = 'session_modal_dismiss';
    public const SESSION_KEY = 'session_modal';

    public const CONTEXT_READHESION = 'readhesion';

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $token = $event->getAuthenticationToken();
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
            $request->getSession()->set(self::SESSION_KEY, self::CONTEXT_READHESION);
        }
    }
}
