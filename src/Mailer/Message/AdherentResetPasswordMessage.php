<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentResetPasswordMessage extends Message
{
    public static function createFromAdherent(Adherent $adherent, string $resetPasswordLink): self
    {
        return new self(
            Uuid::uuid4(),
            '54686',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Réinitialisez votre mot de passe',
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
                'reset_link' => $resetPasswordLink,
            ]
        );
    }
}
