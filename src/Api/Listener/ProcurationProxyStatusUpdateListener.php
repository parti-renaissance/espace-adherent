<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ProcurationV2\Proxy;
use App\Procuration\V2\ProcurationHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProcurationProxyStatusUpdateListener implements EventSubscriberInterface
{
    public function __construct(
        public readonly ProcurationHandler $procurationHandler
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onProxyChange', EventPriorities::POST_WRITE]];
    }

    public function onProxyChange(ViewEvent $viewEvent): void
    {
        $procurationProxy = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();

        if (
            !$procurationProxy instanceof Proxy
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        $this->procurationHandler->updateProxyStatus($procurationProxy);
    }
}
