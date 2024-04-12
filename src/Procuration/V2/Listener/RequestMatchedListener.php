<?php

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\MatchingHistoryHandler;
use App\Procuration\V2\ProcurationNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestMatchedListener implements EventSubscriberInterface
{
    private ?Proxy $proxyBeforeUpdate = null;

    public function __construct(
        private readonly ProcurationNotifier $procurationNotifier,
        private readonly MatchingHistoryHandler $matchingHistoryHandler
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::REQUEST_BEFORE_UPDATE => ['onBeforeUpdate', -256],
            ProcurationEvents::REQUEST_AFTER_UPDATE => ['onAfterUpdate', -256],
        ];
    }

    public function onBeforeUpdate(ProcurationEvent $event): void
    {
        $request = $event->procuration;

        if (!$request instanceof Request) {
            return;
        }

        $this->proxyBeforeUpdate = $request->proxy;
    }

    public function onAfterUpdate(ProcurationEvent $event): void
    {
        $request = $event->procuration;

        if (!$request instanceof Request) {
            return;
        }

        $proxy = $request->proxy;

        if (
            null !== $this->proxyBeforeUpdate
            && $proxy !== $this->proxyBeforeUpdate
        ) {
            $this->matchingHistoryHandler->createUnmatch($request, $this->proxyBeforeUpdate);

            $this->procurationNotifier->sendUnmatchConfirmation($request, $this->proxyBeforeUpdate);
        }

        if (
            null !== $proxy
            && $proxy !== $this->proxyBeforeUpdate
        ) {
            $this->matchingHistoryHandler->createMatch($request, $proxy);

            $this->procurationNotifier->sendMatchConfirmation($request, $proxy);
        }
    }
}
