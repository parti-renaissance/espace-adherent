<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentResetPasswordConfirmationMessage extends Message
{
    public static function create(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            static::getTemplateVars($adherent)
        );
    }

    private static function getTemplateVars(Adherent $adherent): array
    {
        return [
            'first_name' => self::escape($adherent->getFirstName()),
        ];
    }
}
