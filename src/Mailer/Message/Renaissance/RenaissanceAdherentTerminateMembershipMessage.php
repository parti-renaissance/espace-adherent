<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Symfony\Component\Uid\Uuid;

class RenaissanceAdherentTerminateMembershipMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(Adherent $adherent): self
    {
        return new self(
            Uuid::v4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre départ de Renaissance !',
            [],
            static::getRecipientVars($adherent->getFirstName())
        );
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
