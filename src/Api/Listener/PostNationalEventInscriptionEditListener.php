<?php

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\NationalEvent\EventInscription;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
        $eventInscription = $viewEvent->controllerArgumentsEvent->getArguments()[0] ?? null;

        if (
            '_api_/v3/national_event_inscriptions/{uuid}{._format}_put' !== $viewEvent->getRequest()->attributes->get('_api_operation_name')
            || !$eventInscription instanceof EventInscription
        ) {
            return;
        }

        if ($eventInscription->event->mailchimpSync) {
            $this->messageBus->dispatch(new NationalEventInscriptionChangeCommand($eventInscription->getUuid()));
        }
    }
}
