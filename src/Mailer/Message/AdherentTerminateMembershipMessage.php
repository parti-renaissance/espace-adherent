<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentTerminateMembershipMessage extends Message
{
    public static function createFromAdherent(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            '187353',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre départ d\'En Marche !',
            static::getTemplateVars(),
            static::getRecipientVars($adherent->getFirstName())
        );
    }

    private static function getTemplateVars(): array
    {
        return [
            'target_firstname' => '',
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
