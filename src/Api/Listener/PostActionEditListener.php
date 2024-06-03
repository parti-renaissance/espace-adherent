<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Action\Action;
use App\JeMarche\Command\ActionCreatedNotificationCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostActionEditListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onActionEdit', EventPriorities::POST_WRITE]];
    }

    public function onActionEdit(ViewEvent $viewEvent): void
    {
        $action = $viewEvent->getControllerResult();

        if (!$action instanceof Action) {
            return;
        }

        if (!$viewEvent->isMainRequest() || !$viewEvent->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        if ($action->isCancelled()) {
            return;
        }

        $this->messageBus->dispatch(new ActionCreatedNotificationCommand($action->getUuid()));
    }
}
