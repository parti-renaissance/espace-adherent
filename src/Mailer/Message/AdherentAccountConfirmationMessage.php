<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountConfirmationMessage extends Message
{
    public static function create(
        Adherent $adherent,
        int $adherentsCount = 0
    ): self {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            static::getTemplateVars($adherent, $adherentsCount)
        );
    }

    private static function getTemplateVars(
        Adherent $adherent,
        int $adherentsCount
    ): array {
        return [
            'adherents_count' => $adherentsCount,
            'first_name' => self::escape($adherent->getFirstName()),
        ];
    }
}
