<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Action\ActionEvent;
use App\Entity\Action\Action;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PostActionEditListener implements EventSubscriberInterface
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onActionEdit', EventPriorities::POST_WRITE]];
    }

    public function onActionEdit(ViewEvent $viewEvent): void
    {
        $action = $viewEvent->getControllerResult();

        if (
            !$viewEvent->isMainRequest()
            || !$action instanceof Action
            || $action->isCancelled()
        ) {
            return;
        }

        $requestMethod = $viewEvent->getRequest()->getMethod();

        if (Request::METHOD_POST === $requestMethod) {
            $this->dispatcher->dispatch(new ActionEvent($action->getAuthor(), $action), Events::ACTION_CREATED);
        } elseif (Request::METHOD_PUT === $requestMethod) {
            $this->dispatcher->dispatch(new ActionEvent($action->getAuthor(), $action), Events::ACTION_UPDATED);
        }
    }
}
