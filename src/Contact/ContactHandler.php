<?php

namespace App\Contact;

use App\Entity\Contact;
use App\Membership\Contact\ContactRegistrationCommand;
use App\SendInBlue\Command\ContactSynchronisationCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class ContactHandler
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatchSynchronisation(Contact $contact): void
    {
        $this->bus->dispatch(new ContactSynchronisationCommand($contact->getUuid()));
    }

    public function dispatchProcess(Contact $contact): void
    {
        $this->bus->dispatch(new ContactRegistrationCommand($contact->getUuid()));
    }
}
