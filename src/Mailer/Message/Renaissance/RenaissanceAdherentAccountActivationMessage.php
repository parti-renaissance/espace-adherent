<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceAdherentAccountActivationMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre compte Renaissance',
            [],
            static::getRecipientVars($adherent->getFirstName(), $confirmationLink)
        );
    }

    private static function getRecipientVars(string $firstName, string $confirmationLink): array
    {
        return [
            'first_name' => self::escape($firstName),
            'activation_link' => $confirmationLink,
        ];
    }
}
