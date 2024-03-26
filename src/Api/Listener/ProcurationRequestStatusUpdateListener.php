<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ProcurationV2\Request as ProcurationRequest;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\Event\RequestEvent as ProcurationRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProcurationRequestStatusUpdateListener implements EventSubscriberInterface
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
        $procurationRequest = $request->attributes->get('data');

        if (
            !$procurationRequest instanceof ProcurationRequest
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        $this->eventDispatcher->dispatch(new ProcurationRequestEvent($procurationRequest), ProcurationEvents::REQUEST_BEFORE_UPDATE);
    }

    public function onAfterUpdate(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();
        $procurationRequest = $viewEvent->getControllerResult();

        if (
            !$procurationRequest instanceof ProcurationRequest
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        $this->eventDispatcher->dispatch(new ProcurationRequestEvent($procurationRequest), ProcurationEvents::REQUEST_AFTER_UPDATE);
    }
}
