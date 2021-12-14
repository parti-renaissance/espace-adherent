<?php

namespace App\Mailer\Message\JeMengage;

use App\Entity\Adherent;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class JeMengageResetPasswordMessage extends AbstractJeMengageMessage
{
    public static function createFromAdherent(Adherent $user, string $createPasswordLink): Message
    {
        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $user->getEmailAddress(),
            $user->getFullName(),
            'Réinitialisation de votre mot de passe',
            [
                'first_name' => self::escape($user->getFirstName()),
                'create_password_link' => $createPasswordLink,
            ]
        ));
    }
}
