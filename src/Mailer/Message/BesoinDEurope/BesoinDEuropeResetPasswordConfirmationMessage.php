<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class BesoinDEuropeResetPasswordConfirmationMessage extends AbstractBesoinDEuropeMessage
{
    public static function createFromAdherent(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '',
            ['first_name' => self::escape($adherent->getFirstName())]
        );
    }
}
