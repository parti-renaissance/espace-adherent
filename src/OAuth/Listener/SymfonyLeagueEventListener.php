<?php

namespace App\OAuth\Listener;

use League\Event\EventInterface;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SymfonyLeagueEventListener implements ListenerProviderInterface
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor): void
    {
        $listenerAcceptor->addListener('*', $this->dispatchLeagueEventWithSymfonyEventDispatcher(...));
    }

    private function dispatchLeagueEventWithSymfonyEventDispatcher(EventInterface $event): void
    {
        $this->eventDispatcher->dispatch($event, $event->getName());
    }
}
