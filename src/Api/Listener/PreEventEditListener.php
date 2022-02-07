<?php

namespace App\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Event\BaseEvent;
use App\Event\EventEvent;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PreEventEditListener implements EventSubscriberInterface
{
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['onBeforeEventChange', EventPriorities::PRE_DESERIALIZE]];
    }

    public function onBeforeEventChange(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();

        if ('put' !== $request->attributes->get('_api_item_operation_name')
            || BaseEvent::class !== $request->attributes->get('_api_resource_class')) {
            return;
        }

        $event = $request->attributes->get('data');

        $this->dispatcher->dispatch(new EventEvent($event->getAuthor(), $event), Events::EVENT_PRE_UPDATE);
    }
}
