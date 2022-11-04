<?php

namespace App\Procuration\Listener;

use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Procuration\Command\NewProcurationObjectCommand;
use App\Procuration\Event\ProcurationEvents;
use App\Procuration\Event\ProcurationProxyEvent;
use App\Procuration\Event\ProcurationRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProcurationListener implements EventSubscriberInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::PROXY_REGISTRATION => ['onProxyRegistration', -256],
            ProcurationEvents::REQUEST_REGISTRATION => ['onRequestRegistration', -256],
        ];
    }

    public function onProxyRegistration(ProcurationProxyEvent $event): void
    {
        $this->bus->dispatch(new NewProcurationObjectCommand(ProcurationProxy::class, $event->getProxy()->getId()));
    }

    public function onRequestRegistration(ProcurationRequestEvent $event): void
    {
        $this->bus->dispatch(new NewProcurationObjectCommand(ProcurationRequest::class, $event->getRequest()->getId()));
    }
}
