<?php

namespace App\Jecoute\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Jecoute\News;
use App\Jecoute\NewsHandler;
use App\Scope\AuthorizationChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PreWriteNewsListener implements EventSubscriberInterface
{
    private NewsHandler $handler;
    private RequestStack $requestStack;
    private AuthorizationChecker $authorizationChecker;

    public function __construct(
        NewsHandler $handler,
        RequestStack $requestStack,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->handler = $handler;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['preWrite', EventPriorities::PRE_WRITE]];
    }

    public function preWrite(ViewEvent $event): void
    {
        $news = $event->getControllerResult();

        if (!$news instanceof News
            || !$event->getRequest()->isMethod(Request::METHOD_POST)
        ) {
            return;
        }

        $this->handler->buildTopic($news);
    }
}
