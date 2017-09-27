<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountActivationMessage extends Message
{
    public static function createFromAdherent(Adherent $adherent, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            '54665',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Plus qu\'une étape',
            static::getTemplateVars(),
            static::getRecipientVars($adherent->getFirstName(), $confirmationLink)
        );
    }

    private static function getTemplateVars(): array
    {
        return [
            'target_firstname' => '',
            'confirmation_link' => '',
        ];
    }

    private static function getRecipientVars(string $firstName, string $confirmationLink): array
    {
        return [
            'target_firstname' => self::escape($firstName),
            'confirmation_link' => $confirmationLink,
        ];
    }
}
