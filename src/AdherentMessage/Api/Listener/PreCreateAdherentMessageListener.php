<?php

declare(strict_types=1);

namespace App\AdherentMessage\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\AdherentMessage\Events;
use App\AdherentMessage\MessageEvent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PreCreateAdherentMessageListener implements EventSubscriberInterface
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['dispatchPreCreateEvent', EventPriorities::PRE_WRITE]];
    }

    public function dispatchPreCreateEvent(ViewEvent $event): void
    {
        $message = $event->getControllerResult();

        if (
            !$event->getRequest()->isMethod(Request::METHOD_POST)
            || !$message instanceof AdherentMessageInterface
            || $message->getId()
        ) {
            return;
        }

        $this->eventDispatcher->dispatch(new MessageEvent($message), Events::MESSAGE_PRE_CREATE);
    }
}
