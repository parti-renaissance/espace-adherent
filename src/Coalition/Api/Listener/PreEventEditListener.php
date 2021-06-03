<?php

namespace App\Coalition\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Event\BaseEvent;
use App\Event\EventEvent;
use App\Events;
use App\Repository\Event\BaseEventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PreEventEditListener implements EventSubscriberInterface
{
    private $dispatcher;
    private $baseEventRepository;

    public function __construct(EventDispatcherInterface $dispatcher, BaseEventRepository $baseEventRepository)
    {
        $this->dispatcher = $dispatcher;
        $this->baseEventRepository = $baseEventRepository;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['onBeforeEventChange', EventPriorities::PRE_DESERIALIZE]];
    }

    public function onBeforeEventChange(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();

        if ('api_platform.action.put_item' !== $request->attributes->get('_controller')
            || BaseEvent::class !== $request->attributes->get('_api_resource_class')) {
            return;
        }

        $event = $this->baseEventRepository->findOneByUuid($request->get('id'));

        $this->dispatcher->dispatch(
            new EventEvent($event->getAuthor(), $event),
           Events::EVENT_PRE_UPDATE
        );
    }
}
