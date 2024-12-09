<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceAdherentAccountConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(Adherent $adherent): self
    {
        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Bienvenue chez Renaissance !',
            ['target_firstname' => self::escape($adherent->getFirstName())]
        );

        $message->setSenderName('Gabriel Attal');

        return $message;
    }
}
