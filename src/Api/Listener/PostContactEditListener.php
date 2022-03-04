<?php

namespace App\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Contact;
use App\Membership\Contact\ContactRegistrationCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class PostContactEditListener implements EventSubscriberInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['onContactChange', EventPriorities::POST_WRITE]];
    }

    public function onContactChange(ViewEvent $viewEvent): void
    {
        $contact = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();

        if (Request::METHOD_PUT !== $request->getMethod() || !$contact instanceof Contact) {
            return;
        }

        // We assume a contact is complete once the birthdate is known
        if (null !== $contact->getBirthdate()) {
            $this->bus->dispatch(new ContactRegistrationCommand($contact->getUuid()));
        }
    }
}
