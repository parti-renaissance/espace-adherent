<?php

declare(strict_types=1);

namespace App\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Contact\ContactHandler;
use App\Entity\Contact;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostContactEditListener implements EventSubscriberInterface
{
    private ContactHandler $contactHandler;

    public function __construct(ContactHandler $contactHandler)
    {
        $this->contactHandler = $contactHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onContactChange', EventPriorities::POST_WRITE]];
    }

    public function onContactChange(ViewEvent $viewEvent): void
    {
        $contact = $viewEvent->getControllerResult();
        $request = $viewEvent->getRequest();

        if (
            !$contact instanceof Contact
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return;
        }

        // We assume a contact is complete once the birthdate is known
        if (null !== $contact->getBirthdate()) {
            $this->contactHandler->dispatchProcess($contact);
        }
    }
}
