<?php

namespace App\Event;

use App\Entity\EventRegistration;

class EventRegistrationFactory
{
    public function createFromCommand(EventRegistrationCommand $command): EventRegistration
    {
        return new EventRegistration(
            $command->getRegistrationUuid(),
            $command->getEvent(),
            $command->getFirstName(),
            $command->getLastName(),
            $command->getEmailAddress(),
            $command->isNewsletterSubscriber(),
            $command->getAdherentUuid()
        );
    }
}
