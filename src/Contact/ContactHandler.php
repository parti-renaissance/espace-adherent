<?php

declare(strict_types=1);

namespace App\Contact;

use App\Entity\Contact;
use App\Membership\Contact\ContactRegistrationCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class ContactHandler
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatchProcess(Contact $contact): void
    {
        $this->bus->dispatch(new ContactRegistrationCommand($contact->getUuid()));
    }
}
