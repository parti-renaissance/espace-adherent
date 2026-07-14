<?php

declare(strict_types=1);

namespace App\Analytics\PostHog\EventSubscriber;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Wire login_succeeded / login_failed / logout_completed / magic_link_login_succeeded
 * via Symfony 7.4 firewall events (LoginSuccessEvent, LoginFailureEvent, LogoutEvent).
 *
 * Pattern cohérent avec UserActionHistorySubscriber existant.
 * Auto-configuré via `_defaults: autoconfigure: true` du repo — pas de tag explicit
 * requis (Symfony détecte EventSubscriberInterface).
 *
 * Cf. spec §8.1 + review Opus C3 (SecurityController::loginAction PAS approprié).
 */
final class AuthEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly PostHogService $service)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof Adherent) {
            return;
        }

        $method = $this->detectAuthMethod($event);
        $eventName = 'magic-link' === $method
            ? PostHogEventName::MAGIC_LINK_LOGIN_SUCCEEDED
            : PostHogEventName::LOGIN_SUCCEEDED;

        $this->service->captureServerSide($eventName, ['method' => $method], $user);
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $this->service->captureServerSide(
            PostHogEventName::LOGIN_FAILED,
            ['reason' => $this->classifyError($event->getException())],
        );
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        $this->service->captureServerSide(
            PostHogEventName::LOGOUT_COMPLETED,
            [],
            $user instanceof Adherent ? $user : null,
        );
    }

    private function detectAuthMethod(LoginSuccessEvent $event): string
    {
        $authenticatorClass = $event->getAuthenticator()::class;

        return match (true) {
            str_contains($authenticatorClass, 'MagicLink') => 'magic-link',
            str_contains($authenticatorClass, 'OAuth') => 'oauth',
            default => 'form',
        };
    }

    private function classifyError(AuthenticationException $e): string
    {
        return match (true) {
            $e instanceof BadCredentialsException => 'bad_credentials',
            default => 'unknown',
        };
    }
}
