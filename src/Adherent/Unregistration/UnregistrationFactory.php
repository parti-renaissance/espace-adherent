<?php

namespace App\Adherent\Unregistration;

use App\Entity\Adherent;
use App\Entity\Unregistration;

class UnregistrationFactory
{
    public static function createFromUnregistrationCommandAndAdherent(
        UnregistrationCommand $command,
        Adherent $adherent
    ): Unregistration {
        return new Unregistration(
            $adherent->getUuid(),
            Adherent::createUuid($adherent->getEmailAddress()),
            $command->getReasons(),
            $command->getComment(),
            $adherent->getRegisteredAt(),
            $adherent->getPostAddress()->getPostalCode(),
            $adherent->isAdherent(),
            $adherent->isRenaissanceUser(),
            $adherent->getReferentTags()->toArray(),
            $command->getExcludedBy()
        );
    }

    public static function createFromAdherent(Adherent $adherent, string $comment = null): self
    {
        return new Unregistration(
            $adherent->getUuid(),
            Adherent::createUuid($adherent->getEmailAddress()),
            ['autre'],
            $comment,
            $adherent->getRegisteredAt(),
            $adherent->getPostAddress()->getPostalCode(),
            $adherent->isAdherent(),
            $adherent->isRenaissanceUser(),
            $adherent->getReferentTags()->toArray()
        );
    }
}
