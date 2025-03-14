<?php

namespace App\Adherent\Unregistration;

use App\Entity\Adherent;
use App\Entity\Unregistration;

class UnregistrationFactory
{
    public static function createFromUnregistrationCommandAndAdherent(
        UnregistrationCommand $command,
        Adherent $adherent,
    ): Unregistration {
        return new Unregistration(
            $adherent->getUuid(),
            Adherent::createUuid($adherent->getEmailAddress()),
            $command->getReasons(),
            $command->getComment(),
            $adherent->getRegisteredAt(),
            $adherent->getPostAddress()->getPostalCode(),
            $adherent->isRenaissanceAdherent()
                ? TypeEnum::ADHERENT
                : TypeEnum::SYMPATHIZER,
            $adherent->tags,
            $adherent->isRenaissanceAdherent(),
            $adherent->isRenaissanceUser(),
            $command->getExcludedBy()
        );
    }

    public static function createFromAdherent(Adherent $adherent, ?string $comment = null): Unregistration
    {
        return new Unregistration(
            $adherent->getUuid(),
            Adherent::createUuid($adherent->getEmailAddress()),
            ['autre'],
            $comment,
            $adherent->getRegisteredAt(),
            $adherent->getPostAddress()->getPostalCode(),
            $adherent->isRenaissanceAdherent()
                ? TypeEnum::ADHERENT
                : TypeEnum::SYMPATHIZER,
            $adherent->tags,
            $adherent->isRenaissanceAdherent(),
            $adherent->isRenaissanceUser(),
        );
    }
}
