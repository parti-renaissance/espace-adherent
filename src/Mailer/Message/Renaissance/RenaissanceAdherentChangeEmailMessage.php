<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class RenaissanceAdherentChangeEmailMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(Adherent $adherent, string $newEmail, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $newEmail,
            $adherent->getFullName(),
            'Validez votre nouvelle adresse e-mail',
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
