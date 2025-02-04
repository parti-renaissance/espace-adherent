<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Event\Event;
use App\Event\EventEvent;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PostEventEditListener implements EventSubscriberInterface
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onEventChange', EventPriorities::POST_WRITE]];
    }

    public function onEventChange(ViewEvent $viewEvent): void
    {
        /** @var Event $event */
        $event = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();

        if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT]) || !$event instanceof Event) {
            return;
        }

        $operationName = $request->attributes->get('_api_operation_name');

        $this->dispatcher->dispatch(
            new EventEvent($event->getAuthor(), $event),
            '_api_/v3/events_post' === $operationName ? Events::EVENT_CREATED : Events::EVENT_UPDATED
        );
    }
}
