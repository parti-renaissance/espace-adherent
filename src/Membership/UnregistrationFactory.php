<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Unregistration;

class UnregistrationFactory
{
    public function createFromUnregistrationCommandAndAdherent(
        UnregistrationCommand $command,
        Adherent $adherent
    ): Unregistration {
        $unregistration = new Unregistration(
            $adherent->getUuid(),
            $command->getReasons(),
            $command->getComment(),
            $adherent->getRegisteredAt(),
            $adherent->getPostAddress()->getPostalCode(),
            $adherent->isAdherent(),
            $adherent->getReferentTags()->toArray(),
            $command->getExcludedBy()
        );

        return $unregistration;
    }
}
