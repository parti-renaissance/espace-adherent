<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

class RenaissanceReAdhesionConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(Adherent $adherent): Message
    {
        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Et une année de plus !',
            ['first_name' => self::escape($adherent->getFirstName())]
        ));
    }
}
