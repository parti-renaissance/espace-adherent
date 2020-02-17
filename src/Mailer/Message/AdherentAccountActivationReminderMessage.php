<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountActivationReminderMessage extends Message
{
    public static function create(Adherent $adherent, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre compte En-Marche.fr',
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
