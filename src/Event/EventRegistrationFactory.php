<?php

namespace App\Event;

use App\Entity\Event\EventRegistration;

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
            $command->getPostalCode(),
            $command->isJoinNewsletter(),
            $command->getAdherent(),
            $command->getAuthAppCode(),
            status: $command->status,
        );
    }
}
