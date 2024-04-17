<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class BesoinDEuropeActivationReminderMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(Adherent $adherent, string $loginLink): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'login_link' => $loginLink,
            ],
        );
    }
}
