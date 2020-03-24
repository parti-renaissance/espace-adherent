<?php

namespace AppBundle\Adherent\Unregistration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Unregistration;

class UnregistrationFactory
{
    public static function createFromUnregistrationCommandAndAdherent(
        UnregistrationCommand $command,
        Adherent $adherent
    ): Unregistration {
        return new Unregistration(
            $adherent->getUuid(),
            $command->getReasons(),
            $command->getComment(),
            $adherent->getRegisteredAt(),
            $adherent->getPostAddress()->getPostalCode(),
            $adherent->isAdherent(),
            $adherent->getReferentTags()->toArray(),
            $command->getExcludedBy()
        );
    }
}
