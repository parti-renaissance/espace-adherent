<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class BesoinDEuropeAccountCreatedMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            sprintf('Bienvenue %s !', $firstName = self::escape($adherent->getFirstName())),
            [
                'first_name' => $firstName,
            ],
        );
    }
}
