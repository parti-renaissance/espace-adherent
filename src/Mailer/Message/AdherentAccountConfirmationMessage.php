<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountConfirmationMessage extends Message
{
    public static function createFromAdherent(Adherent $adherent, int $adherentsCount = 0): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            self::getTemplateVars($adherentsCount),
            self::getRecipientVars($adherent->getFirstName())
        );
    }

    private static function getTemplateVars(int $adherentsCount = 0): array
    {
        return [
            'adherents_count' => $adherentsCount,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
