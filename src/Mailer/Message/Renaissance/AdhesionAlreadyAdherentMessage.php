<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class AdhesionAlreadyAdherentMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Vous êtes déjà adhérent',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'this_year' => date('Y'),
            ],
        );
    }
}
