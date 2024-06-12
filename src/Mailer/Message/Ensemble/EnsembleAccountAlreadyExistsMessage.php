<?php

namespace App\Mailer\Message\Ensemble;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class EnsembleAccountAlreadyExistsMessage extends AbstractEnsembleMessage
{
    public static function create(Adherent $adherent, string $magicLink, string $forgotPasswordLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Vous possédez déjà un compte',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'magic_link' => $magicLink,
                'forgot_password_link' => $forgotPasswordLink,
            ],
        );
    }
}
