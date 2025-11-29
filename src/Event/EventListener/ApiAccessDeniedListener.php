<?php

declare(strict_types=1);

namespace App\Event\EventListener;

use App\Entity\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiAccessDeniedListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        $eventEntity = $request->attributes->get('data');

        if (
            $exception instanceof AccessDeniedHttpException
            && '_api_/v3/events/{uuid}_get' === $request->attributes->get('_api_operation_name')
            && $eventEntity instanceof Event
            && $eventEntity->isInvitation()
            && $eventEntity->agora
        ) {
            $event->setResponse(new JsonResponse([
                'title' => 'An error occurred',
                'detail' => \sprintf('Vous devez faire partie de l\'agora %s pour accéder à cet événement.', $eventEntity->agora->getName()),
                'status' => 403,
                'type' => '/errors/403',
                'key' => 'event.agora.access_denied',
            ], Response::HTTP_FORBIDDEN));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }
}
