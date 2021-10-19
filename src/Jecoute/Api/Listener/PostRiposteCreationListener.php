<?php

namespace App\Jecoute\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Jecoute\Riposte;
use App\Jecoute\RiposteHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostRiposteCreationListener implements EventSubscriberInterface
{
    private RiposteHandler $riposteHandler;

    public function __construct(RiposteHandler $riposteHandler)
    {
        $this->riposteHandler = $riposteHandler;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['sendNotification', EventPriorities::POST_WRITE]];
    }

    public function sendNotification(ViewEvent $event): void
    {
        $riposte = $event->getControllerResult();

        if (
            !$event->getRequest()->isMethod(Request::METHOD_POST)
            || !$riposte instanceof Riposte
        ) {
            return;
        }

        $this->riposteHandler->handleNotification($riposte);
    }
}
