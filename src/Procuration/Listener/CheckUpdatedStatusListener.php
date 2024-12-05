<?php

namespace App\Procuration\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ProcurationV2\AbstractProcuration;
use App\Entity\ProcurationV2\AbstractSlot;
use App\Entity\ProcurationV2\ProxySlot;
use App\Entity\ProcurationV2\RequestSlot;
use App\Procuration\V2\ProcurationHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckUpdatedStatusListener implements EventSubscriberInterface
{
    public function __construct(private readonly ProcurationHandler $procurationHandler)
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

        if ($object->hasMatchedSlot()) {
            $event->setResponse(new JsonResponse([
                'status' => 'error',
                'message' => 'Le statut ne peut pas être modifié si un slot est déjà matché.',
            ], Response::HTTP_BAD_REQUEST));
        }
    }

    public function preWrite(ViewEvent $event): void
    {
        $slot = $event->getControllerResult();

        if (!$slot instanceof AbstractSlot
            || !$event->getRequest()->isMethod(Request::METHOD_PUT)
        ) {
            return;
        }

        if ($slot instanceof ProxySlot) {
            $this->procurationHandler->updateProxyStatus($slot->proxy);

            return;
        }

        if ($slot instanceof RequestSlot) {
            $this->procurationHandler->updateRequestStatus($slot->request);
        }
    }
}
