<?php

namespace App\Jecoute\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Jecoute\News;
use App\Jecoute\NewsHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostWriteNewsListener implements EventSubscriberInterface
{
    private NewsHandler $handler;

    public function __construct(NewsHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['postWrite', EventPriorities::POST_WRITE]];
    }

    public function postWrite(ViewEvent $event): void
    {
        $news = $event->getControllerResult();

        if (!$news instanceof News
            || !$event->getRequest()->isMethod(Request::METHOD_POST)
        ) {
            return;
        }

        $this->handler->handleNotification($news);
    }
}
