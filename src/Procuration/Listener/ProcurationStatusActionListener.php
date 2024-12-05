<?php

namespace App\Procuration\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ProcurationV2\AbstractProcuration;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\ProcurationActionHandler;
use App\Procuration\V2\ProxyStatusEnum;
use App\Procuration\V2\RequestStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProcurationStatusActionListener implements EventSubscriberInterface
{
    private ?ProxyStatusEnum $proxyStatusBeforeUpdate = null;
    private ?RequestStatusEnum $requestStatusBeforeUpdate = null;

    public function __construct(private readonly ProcurationActionHandler $actionHandler)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['preDeserialize', EventPriorities::PRE_DESERIALIZE],
            KernelEvents::VIEW => ['preWrite', EventPriorities::PRE_WRITE],
        ];
    }

    public function preDeserialize(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $operationName = $request->attributes->get('_api_operation_name');

        if (!\in_array($operationName, [
            '_api_/v3/procuration/requests/{uuid}_patch',
            '_api_/v3/procuration/proxies/{uuid}_patch',
        ], true)) {
            return;
        }

        $object = $request->get('data');

        if (!$object instanceof AbstractProcuration) {
            return;
        }

        if ($object instanceof Proxy) {
            $this->proxyStatusBeforeUpdate = $object->status;

            return;
        }

        if ($object instanceof Request) {
            $this->requestStatusBeforeUpdate = $object->status;
        }
    }

    public function preWrite(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $operationName = $request->attributes->get('_api_operation_name');

        if (!\in_array($operationName, [
            '_api_/v3/procuration/requests/{uuid}_patch',
            '_api_/v3/procuration/proxies/{uuid}_patch',
        ], true)) {
            return;
        }

        $object = $event->getControllerResult();

        if (!$object instanceof AbstractProcuration) {
            return;
        }

        if (
            $object instanceof Proxy
            && $object->status !== $this->proxyStatusBeforeUpdate
        ) {
            $this->actionHandler->createProxyStatusUpdateAction($object, $this->proxyStatusBeforeUpdate);

            return;
        }

        if (
            $object instanceof Request
            && $object->status !== $this->requestStatusBeforeUpdate
        ) {
            $this->actionHandler->createRequestStatusUpdateAction($object, $this->requestStatusBeforeUpdate);
        }
    }
}
