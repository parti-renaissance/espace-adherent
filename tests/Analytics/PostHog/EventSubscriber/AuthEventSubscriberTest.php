<?php

declare(strict_types=1);

namespace Tests\App\Analytics\PostHog\EventSubscriber;

use App\Analytics\PostHog\EventSubscriber\AuthEventSubscriber;
use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthEventSubscriberTest extends TestCase
{
    public function testOnLoginSuccessCapturesEvent(): void
    {
        $service = $this->createMock(PostHogService::class);
        $service->expects($this->once())
            ->method('captureServerSide')
            ->with(
                PostHogEventName::LOGIN_SUCCEEDED,
                $this->callback(fn ($props) => isset($props['method'])),
                $this->isInstanceOf(Adherent::class),
            );

        $subscriber = new AuthEventSubscriber($service);
        $user = $this->createMock(Adherent::class);
        $token = new UsernamePasswordToken($user, 'main');
        $request = new Request();
        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $passport = new SelfValidatingPassport(new UserBadge('user@example.com', fn () => $user));
        $event = new LoginSuccessEvent($authenticator, $passport, $token, $request, null, 'main');

        $subscriber->onLoginSuccess($event);
    }

    public function testOnLoginFailureMapsBadCredentials(): void
    {
        $service = $this->createMock(PostHogService::class);
        $service->expects($this->once())
            ->method('captureServerSide')
            ->with(
                PostHogEventName::LOGIN_FAILED,
                $this->callback(fn ($props) => 'bad_credentials' === $props['reason']),
            );

        $subscriber = new AuthEventSubscriber($service);
        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $event = new LoginFailureEvent(
            new BadCredentialsException(),
            $authenticator,
            new Request(),
            null,
            'main',
        );

        $subscriber->onLoginFailure($event);
    }

    public function testOnLogoutCapturesEvent(): void
    {
        $service = $this->createMock(PostHogService::class);
        $service->expects($this->once())
            ->method('captureServerSide')
            ->with(
                PostHogEventName::LOGOUT_COMPLETED,
                [],
                $this->anything(),
            );

        $subscriber = new AuthEventSubscriber($service);
        $event = new LogoutEvent(new Request(), null);
        $subscriber->onLogout($event);
    }
}
