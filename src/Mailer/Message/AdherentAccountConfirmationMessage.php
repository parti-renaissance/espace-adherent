<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountConfirmationMessage extends Message
{
    public static function create(
        Adherent $adherent,
        int $adherentsCount = 0,
        int $committeesCount = 0
    ): self {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            static::getTemplateVars($adherent, $adherentsCount, $committeesCount)
        );
    }

    private static function getTemplateVars(
        Adherent $adherent,
        int $adherentsCount,
        int $committeesCount
    ): array {
        return [
            'adherents_count' => $adherentsCount,
            'committees_count' => $committeesCount,
            'first_name' => self::escape($adherent->getFirstName()),
            'last_name' => self::escape($adherent->getLastName()),
        ];
    }
}
