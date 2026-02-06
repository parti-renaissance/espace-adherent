<?php

declare(strict_types=1);

namespace App\Procuration\Listener;

use App\Entity\Procuration\Proxy;
use App\Procuration\Event\ProcurationEvent;
use App\Procuration\Event\ProcurationEvents;
use App\Procuration\ProcurationHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProxyStatusListener implements EventSubscriberInterface
{
    private Collection $matchedRequests;

    public function __construct(
        private readonly ProcurationHandler $procurationHandler,
    ) {
        $this->matchedRequests = new ArrayCollection();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::PROXY_BEFORE_UPDATE => ['onBeforeUpdate', -256],
            ProcurationEvents::PROXY_AFTER_UPDATE => ['onAfterUpdate', -256],
        ];
    }

    public function onBeforeUpdate(ProcurationEvent $event): void
    {
        $proxy = $event->procuration;

        if (!$proxy instanceof Proxy) {
            return;
        }

        foreach ($proxy->proxySlots as $proxySlot) {
            $request = $proxySlot->requestSlot?->request;

            if ($request && !$this->matchedRequests->contains($request)) {
                $this->matchedRequests->add($request);
            }
        }
    }

    public function onAfterUpdate(ProcurationEvent $event): void
    {
        $proxy = $event->procuration;

        if (!$proxy instanceof Proxy) {
            return;
        }

        foreach ($proxy->proxySlots as $proxySlot) {
            $request = $proxySlot->requestSlot?->request;

            if ($request && !$this->matchedRequests->contains($request)) {
                $this->matchedRequests->add($request);
            }
        }

        $this->procurationHandler->updateProxyStatus($proxy);

        foreach ($this->matchedRequests as $request) {
            $this->procurationHandler->updateRequestStatus($request);
        }
    }
}
