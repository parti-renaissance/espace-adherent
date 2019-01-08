<?php

namespace AppBundle\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        $payload = $event->getData();

        $payload['roles'] = $user->getRoles();

        $event->setData($payload);
    }
}