<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountConfirmationMessage extends MailjetMessage
{
    public static function createFromAdherent(Adherent $adherent, int $adherentsCount = 0, int $committeesCount = 0): self
    {
        return new static(
            Uuid::uuid4(),
            '54673',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmation de votre inscription au mouvement En Marche !',
            static::getTemplateVars($adherentsCount, $committeesCount),
            static::getRecipientVars($adherent->getFirstName(), $adherent->getLastName())
        );
    }

    private static function getTemplateVars(int $adherentsCount = 0, int $committeesCount = 0): array
    {
        return [
            'adherents_count' => $adherentsCount,
            'committees_count' => $committeesCount,
            'target_firstname' => '',
            'target_lastname' => '',
        ];
    }

    private static function getRecipientVars(string $firstName, string $lastName): array
    {
        return [
            'target_firstname' => $firstName,
            'target_lastname' => $lastName,
        ];
    }
}
