<?php

declare(strict_types=1);

namespace App\Procuration\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Procuration\AbstractSlot;
use App\Entity\Procuration\ProxySlot;
use App\Entity\Procuration\RequestSlot;
use App\Procuration\ProcurationActionHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProcurationSlotStatusActionListener implements EventSubscriberInterface
{
    private ?string $statusBeforeUpdate = null;

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
            '_api_/v3/procuration/request_slots/{uuid}_put',
            '_api_/v3/procuration/proxy_slots/{uuid}_put',
        ], true)) {
            return;
        }

        $object = $request->attributes->get('data');

        if (!$object instanceof AbstractSlot) {
            return;
        }

        $this->statusBeforeUpdate = $this->buildStatus($object);
    }

    public function preWrite(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $operationName = $request->attributes->get('_api_operation_name');

        if (!\in_array($operationName, [
            '_api_/v3/procuration/request_slots/{uuid}_put',
            '_api_/v3/procuration/proxy_slots/{uuid}_put',
        ], true)) {
            return;
        }

        $object = $event->getControllerResult();

        if (!$object instanceof AbstractSlot) {
            return;
        }

        $statusAfterUpdate = $this->buildStatus($object);

        if ($statusAfterUpdate === $this->statusBeforeUpdate) {
            return;
        }

        if ($object instanceof ProxySlot) {
            $this->actionHandler->createProxySlotStatusUpdateAction($object, $statusAfterUpdate, $this->statusBeforeUpdate);

            return;
        }

        if ($object instanceof RequestSlot) {
            $this->actionHandler->createRequestSlotStatusUpdateAction($object, $statusAfterUpdate, $this->statusBeforeUpdate);
        }
    }

    private function buildStatus(AbstractSlot $slot): string
    {
        return $slot->manual ? 'manual' : 'pending';
    }
}
