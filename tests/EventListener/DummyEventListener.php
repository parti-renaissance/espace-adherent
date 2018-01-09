<?php

namespace Tests\AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DummyEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [];
    }
}
