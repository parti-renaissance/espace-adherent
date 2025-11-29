<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Action\Action;
use App\JeMengage\Push\Command\NotifyForActionCommand;
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

        if (
            !$viewEvent->isMainRequest()
            || !$action instanceof Action
            || $action->isCancelled()
        ) {
            return;
        }

        if (!\in_array($requestMethod = $viewEvent->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        if (Request::METHOD_POST === $requestMethod) {
            // Action creation
            $this->messageBus->dispatch(new NotifyForActionCommand($action->getUuid(), NotifyForActionCommand::EVENT_CREATE));
        } elseif (Request::METHOD_PUT === $requestMethod) {
            // Action update
            $this->messageBus->dispatch(new NotifyForActionCommand($action->getUuid(), NotifyForActionCommand::EVENT_UPDATE));
        }
    }
}
