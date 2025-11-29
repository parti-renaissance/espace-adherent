<?php

declare(strict_types=1);

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\ProcurationHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestStatusListener implements EventSubscriberInterface
{
    private Collection $matchedProxies;

    public function __construct(
        private readonly ProcurationHandler $procurationHandler,
    ) {
        $this->matchedProxies = new ArrayCollection();
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

        foreach ($request->requestSlots as $requestSlot) {
            $proxy = $requestSlot->proxySlot?->proxy;

            if ($proxy && !$this->matchedProxies->contains($proxy)) {
                $this->matchedProxies->add($proxy);
            }
        }
    }

    public function onAfterUpdate(ProcurationEvent $event): void
    {
        $request = $event->procuration;

        if (!$request instanceof Request) {
            return;
        }

        foreach ($request->requestSlots as $requestSlot) {
            $proxy = $requestSlot->proxySlot?->proxy;

            if ($proxy && !$this->matchedProxies->contains($proxy)) {
                $this->matchedProxies->add($proxy);
            }
        }

        $this->procurationHandler->updateRequestStatus($request);

        foreach ($this->matchedProxies as $proxy) {
            $this->procurationHandler->updateProxyStatus($proxy);
        }
    }
}
