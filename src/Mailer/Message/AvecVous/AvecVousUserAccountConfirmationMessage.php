<?php

namespace App\Mailer\Message\AvecVous;

use App\Entity\Adherent;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class AvecVousUserAccountConfirmationMessage extends AbstractAvecVousMessage
{
    public static function createFromAdherent(Adherent $adherent, string $createPasswordLink): Message
    {
        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '🚀 Activez votre compte Je m’engage',
            [
                'create_password_link' => $createPasswordLink,
            ]
        ));
    }
}
