<?php

namespace AppBundle\Event;

use AppBundle\Entity\EventRegistration;

class EventRegistrationFactory
{
    public function createFromCommand(EventRegistrationCommand $command): EventRegistration
    {
        return new EventRegistration(
            $command->getRegistrationUuid(),
            $command->getEvent(),
            $command->getFirstName(),
            $command->getEmailAddress(),
            $command->getPostalCode(),
            $command->isNewsletterSubscriber(),
            $command->getAdherentUuid()
        );
    }
}
