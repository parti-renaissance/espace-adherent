<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Adherent\Command\UpdateFirebaseTopicsCommand;
use App\Entity\PushToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostPushTokenEditListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onPushTokenEdit', EventPriorities::POST_WRITE]];
    }

    public function onPushTokenEdit(ViewEvent $viewEvent): void
    {
        $pushToken = $viewEvent->getControllerResult();

        if (!$pushToken instanceof PushToken) {
            return;
        }

        if (!$viewEvent->isMainRequest() || !$viewEvent->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        if (!$pushToken->getAdherent()) {
            return;
        }

        $this->messageBus->dispatch(new UpdateFirebaseTopicsCommand($pushToken->getAdherent()->getUuid()));
    }
}
