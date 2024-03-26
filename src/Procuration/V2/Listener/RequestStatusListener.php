<?php

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Proxy;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\Event\RequestEvent;
use App\Procuration\V2\ProcurationHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestStatusListener implements EventSubscriberInterface
{
    private Collection $proxies;

    public function __construct(
        private readonly ProcurationHandler $procurationHandler
    ) {
        $this->proxies = new ArrayCollection();
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
        $proxy = $event->request->proxy;

        if ($proxy instanceof Proxy) {
            $this->proxies->add($proxy);
        }
    }

    public function onAfterUpdate(RequestEvent $event): void
    {
        $request = $event->request;
        $proxy = $request->proxy;

        if ($proxy instanceof Proxy && !$this->proxies->contains($proxy)) {
            $this->proxies->add($proxy);
        }

        $this->procurationHandler->updateRequestStatus($request);

        foreach ($this->proxies as $proxy) {
            $this->procurationHandler->updateProxyStatus($proxy);
        }
    }
}
