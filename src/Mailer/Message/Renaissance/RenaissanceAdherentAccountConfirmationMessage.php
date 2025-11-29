<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

class RenaissanceAdherentAccountConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(Adherent $adherent): Message
    {
        return self::updateSenderInfo(new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Bienvenue chez Renaissance !',
            ['first_name' => self::escape($adherent->getFirstName())]
        ));
    }
}
