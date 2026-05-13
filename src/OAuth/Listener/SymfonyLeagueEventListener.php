<?php

declare(strict_types=1);

namespace App\OAuth\Listener;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SymfonyLeagueEventListener
{
    private const FORWARDED_EVENTS = [
        RequestEvent::CLIENT_AUTHENTICATION_FAILED,
        RequestEvent::USER_AUTHENTICATION_FAILED,
        RequestEvent::REFRESH_TOKEN_CLIENT_FAILED,
        RequestEvent::REFRESH_TOKEN_ISSUED,
        RequestEvent::ACCESS_TOKEN_ISSUED,
    ];

    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function register(AuthorizationServer $server): void
    {
        $registry = $server->getListenerRegistry();

        foreach (self::FORWARDED_EVENTS as $eventName) {
            $registry->subscribeTo($eventName, function (object $event) use ($eventName): void {
                $this->eventDispatcher->dispatch($event, $eventName);
            });
        }
    }
}
