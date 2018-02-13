<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentResetPasswordMessage extends Message
{
    public static function createFromAdherent(Adherent $adherent, string $resetPasswordLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            [],
            self::getRecipientVars($adherent->getFirstName(), $resetPasswordLink)
        );
    }

    private static function getRecipientVars(string $firstName, string $resetPasswordLink): array
    {
        return [
            'target_firstname' => self::escape($firstName),
            'reset_link' => $resetPasswordLink,
        ];
    }
}
