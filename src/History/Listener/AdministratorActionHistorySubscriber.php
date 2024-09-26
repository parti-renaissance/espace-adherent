<?php

namespace App\History\Listener;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\AdministratorActionHistoryHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

class AdministratorActionHistorySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly AdministratorActionHistoryHandler $administratorActionHistoryHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => ['onLoginSuccess', -4096],
            LoginFailureEvent::class => ['onLoginFailure', -4096],
            SwitchUserEvent::class => ['onSwitchUser', -4096],
            KernelEvents::RESPONSE => ['onKernelResponse', -4096],
        ];
    }

    public function onLoginSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof Administrator) {
            return;
        }

        $this->administratorActionHistoryHandler->createLoginSuccess($user);
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        /** @var UserBadge|null $userBadge */
        $userBadge = $event->getPassport()?->getBadge(UserBadge::class);

        $user = $userBadge?->getUser();

        if (!$user instanceof Administrator) {
            return;
        }

        $this->administratorActionHistoryHandler->createLoginFailure($user);
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        $targetUser = $event->getTargetUser();

        if ($user instanceof Administrator && $targetUser instanceof Adherent) {
            $this->administratorActionHistoryHandler->createImpersonationStart($user, $targetUser);

            return;
        }

        if ($user instanceof Adherent && $targetUser instanceof Administrator) {
            $this->administratorActionHistoryHandler->createImpersonationEnd($user, $targetUser);
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->get('_route');

        if (!preg_match('/^admin_(.)+_export$/', $routeName)) {
            return;
        }

        $administrator = $this->security->getUser();

        if (!$administrator instanceof Administrator) {
            return;
        }

        $this->administratorActionHistoryHandler->createExport($administrator, $routeName, $request->query->all());
    }
}
