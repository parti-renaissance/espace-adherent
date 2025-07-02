<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\NationalEvent\EventInscription;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostNationalEventInscriptionEditListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onNationalEventInscriptionEdit', EventPriorities::POST_WRITE]];
    }

    public function onNationalEventInscriptionEdit(ViewEvent $viewEvent): void
    {
        $eventInscription = $viewEvent->getControllerResult();

        if (
            !$viewEvent->isMainRequest()
            || !$eventInscription instanceof EventInscription
            || !\in_array($viewEvent->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        if ($eventInscription->event->mailchimpSync) {
            $this->messageBus->dispatch(new NationalEventInscriptionChangeCommand($eventInscription->getUuid()));
        }
    }
}
