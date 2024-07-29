<?php

namespace App\Mailer\Message\Ensemble;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class EnsembleAccountCreatedMessage extends AbstractEnsembleMessage
{
    public static function create(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            \sprintf('Bienvenue %s !', $firstName = self::escape($adherent->getFirstName())),
            [
                'first_name' => $firstName,
            ],
        );
    }
}
