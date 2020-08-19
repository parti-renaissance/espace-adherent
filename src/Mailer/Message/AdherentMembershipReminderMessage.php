<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentMembershipReminderMessage extends Message
{
    public static function create(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre compte En-Marche.fr',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
            ]
        );
    }
}
