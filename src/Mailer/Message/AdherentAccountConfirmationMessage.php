<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountConfirmationMessage extends Message
{
    public static function createFromAdherent(
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
            'target_firstname' => self::escape($adherent->getFirstName()),
            'target_lastname' => self::escape($adherent->getLastName()),
        ];
    }
}
