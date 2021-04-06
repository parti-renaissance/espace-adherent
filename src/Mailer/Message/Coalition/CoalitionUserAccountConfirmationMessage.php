<?php

namespace App\Mailer\Message\Coalition;

use App\Entity\Adherent;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class CoalitionUserAccountConfirmationMessage extends AbstractCoalitionMessage
{
    public static function createFromAdherent(Adherent $adherent, string $createPasswordLink): Message
    {
        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre adresse email',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'create_password_link' => $createPasswordLink,
            ]
        ));
    }
}
