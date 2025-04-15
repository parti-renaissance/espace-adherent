<?php

namespace App\AppSession\Listener;

use App\AppSession\Manager;
use League\OAuth2\Server\RequestAccessTokenEvent;
use League\OAuth2\Server\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RefreshAppSessionListener implements EventSubscriberInterface
{
    public function __construct(private readonly Manager $manager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::ACCESS_TOKEN_ISSUED => 'onRequestAccessToken',
        ];
    }

    public function onRequestAccessToken(RequestAccessTokenEvent $event): void
    {
        $this->manager->refreshSession($event->getAccessToken(), $event->getRequest());
    }
}
