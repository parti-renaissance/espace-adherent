<?php

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Proxy;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\ProcurationHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProxyStatusListener implements EventSubscriberInterface
{
    private Collection $requests;

    public function __construct(
        private readonly ProcurationHandler $procurationHandler
    ) {
        $this->requests = new ArrayCollection();
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

        $this->requests = clone $proxy->requests;
    }

    public function onAfterUpdate(ProcurationEvent $event): void
    {
        $proxy = $event->procuration;

        if (!$proxy instanceof Proxy) {
            return;
        }

        foreach ($proxy->requests as $request) {
            if (!$this->requests->contains($request)) {
                $this->requests->add($request);
            }
        }

        $this->procurationHandler->updateProxyStatus($proxy);

        foreach ($this->requests as $request) {
            $this->procurationHandler->updateRequestStatus($request);
        }
    }
}
