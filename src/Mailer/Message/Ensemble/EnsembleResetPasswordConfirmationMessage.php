<?php

namespace App\Mailer\Message\Ensemble;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class EnsembleResetPasswordConfirmationMessage extends AbstractEnsembleMessage
{
    public static function createFromAdherent(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Mot de passe modifié',
            ['first_name' => self::escape($adherent->getFirstName())]
        );
    }
}
