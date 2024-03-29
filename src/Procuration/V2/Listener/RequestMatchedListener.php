<?php

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Proxy;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\Event\RequestEvent;
use App\Procuration\V2\ProcurationNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestMatchedListener implements EventSubscriberInterface
{
    private ?Proxy $proxyBeforeUpdate = null;

    public function __construct(
        private readonly ProcurationNotifier $procurationNotifier
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::REQUEST_BEFORE_UPDATE => ['onBeforeUpdate', -256],
            ProcurationEvents::REQUEST_AFTER_UPDATE => ['onAfterUpdate', -256],
        ];
    }

    public function onBeforeUpdate(RequestEvent $event): void
    {
        $request = $event->request;

        $this->proxyBeforeUpdate = $request->proxy;
    }

    public function onAfterUpdate(RequestEvent $event): void
    {
        $request = $event->request;
        $proxy = $request->proxy;

        if (
            null !== $this->proxyBeforeUpdate
            && $proxy !== $this->proxyBeforeUpdate
        ) {
            $this->procurationNotifier->sendUnmatchConfirmation($request, $this->proxyBeforeUpdate);
        }

        if (
            null !== $proxy
            && $proxy !== $this->proxyBeforeUpdate
        ) {
            $this->procurationNotifier->sendMatchConfirmation($request, $proxy);
        }
    }
}
