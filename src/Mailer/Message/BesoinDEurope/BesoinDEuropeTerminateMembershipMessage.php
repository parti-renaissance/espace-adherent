<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class BesoinDEuropeTerminateMembershipMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '',
            [
                'first_name' => self::escape($adherent->getFirstName()),
            ],
        );
    }
}
