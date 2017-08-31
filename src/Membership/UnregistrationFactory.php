<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Unregistration;

class UnregistrationFactory
{
    public function createFromUnregistrationCommandAndAdherent(UnregistrationCommand $command, Adherent $adherent): Unregistration
    {
        $unregistration = new Unregistration(
            $adherent->getUuid(),
            $adherent->getPostAddress()->getPostalCode(),
            $command->getReasons(),
            $command->getComment(),
            $adherent->getRegisteredAt()
        );

        return $unregistration;
    }
}
