<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceReAdhesionConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmation de votre ré-adhésion à Renaissance !',
            [],
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
                'year' => date('Y'),
            ]
        );
    }
}
