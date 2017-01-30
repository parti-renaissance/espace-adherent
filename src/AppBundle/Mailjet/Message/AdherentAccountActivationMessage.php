<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountActivationMessage extends MailjetMessage
{
    public static function createFromAdherent(Adherent $adherent, string $confirmationLink): self
    {
        return new static(
            Uuid::uuid4(),
            '54665',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Finalisez votre inscription au mouvement En Marche !',
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
            'target_firstname' => $firstName,
            'confirmation_link' => $confirmationLink,
        ];
    }
}
