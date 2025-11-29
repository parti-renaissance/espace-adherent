<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceAdherentAccountActivationReminderMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre compte Renaissance',
            [],
            static::getRecipientVars($adherent, $confirmationLink)
        );
    }

    private static function getRecipientVars(Adherent $adherent, string $confirmationLink): array
    {
        return [
            'first_name' => self::escape($adherent->getFirstName()),
            'activation_link' => $confirmationLink,
            'registered_at' => self::formatDate($adherent->getRegisteredAt(), 'd/MM/yyyy'),
        ];
    }
}
