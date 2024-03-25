<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ProcurationV2\Request as ProcurationRequest;
use App\Procuration\V2\ProcurationHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProcurationRequestStatusUpdateListener implements EventSubscriberInterface
{
    public function __construct(
        public readonly ProcurationHandler $procurationHandler
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onRequestChange', EventPriorities::POST_WRITE]];
    }

    public function onRequestChange(ViewEvent $viewEvent): void
    {
        $procurationRequest = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();

        if (
            !$procurationRequest instanceof ProcurationRequest
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        $this->procurationHandler->updateRequestStatus($procurationRequest);
    }
}
