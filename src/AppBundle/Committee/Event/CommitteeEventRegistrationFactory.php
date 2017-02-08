<?php

namespace AppBundle\Committee\Event;

use AppBundle\Entity\CommitteeEventRegistration;

class CommitteeEventRegistrationFactory
{
    public function createFromCommand(CommitteeEventRegistrationCommand $command): CommitteeEventRegistration
    {
        return new CommitteeEventRegistration(
            $command->getRegistrationUuid(),
            $command->getCommitteeEvent(),
            $command->getFirstName(),
            $command->getEmailAddress(),
            $command->getPostalCode(),
            $command->isNewsletterSubscriber(),
            $command->getAdherentUuid()
        );
    }
}
