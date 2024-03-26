<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ProcurationV2\Proxy;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\Event\ProxyEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProcurationProxyStatusUpdateListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onBeforeUpdate', EventPriorities::PRE_DESERIALIZE],
            KernelEvents::VIEW => ['onAfterUpdate', EventPriorities::POST_WRITE],
        ];
    }

    public function onBeforeUpdate(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();
        $proxy = $request->attributes->get('data');

        if (
            !$proxy instanceof Proxy
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        $this->eventDispatcher->dispatch(new ProxyEvent($proxy), ProcurationEvents::PROXY_BEFORE_UPDATE);
    }

    public function onAfterUpdate(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();
        $proxy = $viewEvent->getControllerResult();

        if (
            !$proxy instanceof Proxy
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        $this->eventDispatcher->dispatch(new ProxyEvent($proxy), ProcurationEvents::PROXY_AFTER_UPDATE);
    }
}
