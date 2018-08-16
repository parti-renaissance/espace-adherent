<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountActivationMessage extends Message
{
    public static function createFromAdherent(Adherent $adherent, string $confirmationLink): self
    {
        return static::create($adherent, '292269', $confirmationLink);
    }

    public static function createReminderFromAdherent(Adherent $adherent, string $confirmationLink): self
    {
        return static::create($adherent, '501948', $confirmationLink);
    }

    private static function create(
        Adherent $adherent,
        string $templateId,
        string $confirmationLink,
        string $subject = 'Confirmez votre compte En-Marche.fr'
    ): self {
        return new self(
            Uuid::uuid4(),
            $templateId,
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            $subject,
            static::getTemplateVars(),
            static::getRecipientVars($adherent->getFirstName(), $confirmationLink)
        );
    }

    private static function getTemplateVars(): array
    {
        return [
            'first_name' => '',
            'activation_link' => '',
        ];
    }

    private static function getRecipientVars(string $firstName, string $confirmationLink): array
    {
        return [
            'first_name' => self::escape($firstName),
            'activation_link' => $confirmationLink,
        ];
    }
}
