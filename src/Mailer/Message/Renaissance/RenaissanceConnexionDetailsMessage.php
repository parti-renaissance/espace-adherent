<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceConnexionDetailsMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Détails de connexion à votre compte Renaissance',
            [],
            ['first_name' => self::escape($adherent->getFirstName())],
        );
    }
}
