<?php

declare(strict_types=1);

namespace App\Procuration\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Procuration\AbstractProcuration;
use App\Entity\Procuration\Proxy;
use App\Entity\Procuration\Request;
use App\Procuration\ProcurationActionHandler;
use App\Procuration\ProxyStatusEnum;
use App\Procuration\RequestStatusEnum;
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

        $object = $request->attributes->get('data');

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
