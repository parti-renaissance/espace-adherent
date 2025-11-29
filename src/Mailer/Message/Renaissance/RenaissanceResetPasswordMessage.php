<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class RenaissanceResetPasswordMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(Adherent $adherent, string $resetPasswordLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Réinitialisation de votre mot de passe',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'reset_link' => $resetPasswordLink,
            ]
        );
    }
}
