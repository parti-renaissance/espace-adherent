<?php

namespace App\Procuration\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\ProcurationV2\AbstractProcuration;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckUpdatedStatusListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['preDeserialize', EventPriorities::PRE_DESERIALIZE],
        ];
    }

    public function preDeserialize(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $operationName = $request->attributes->get('_api_operation_name');

        if (!\in_array($operationName, ['api_requests_update_status_item'], true)) {
            return;
        }

        if (!($object = $request->get('data')) instanceof AbstractProcuration) {
            return;
        }

        if (
            ($object instanceof Request && $object->proxy)
            || ($object instanceof Proxy && $object->requests->count())
        ) {
            $event->setResponse(new JsonResponse([
                'status' => 'error',
                'message' => 'Le statut d\'une demande complétée ne peut pas être modifié.',
            ], Response::HTTP_BAD_REQUEST));
        }
    }
}
