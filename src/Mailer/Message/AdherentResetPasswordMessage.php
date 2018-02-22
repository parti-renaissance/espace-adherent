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
            static::getTemplateVars($adherent, $resetPasswordLink)
        );
    }

    private static function getTemplateVars(Adherent $adherent, $resetPasswordLink): array
    {
        return [
            'first_name' => self::escape($adherent->getFirstName()),
            'reset_link' => $resetPasswordLink,
        ];
    }
}
