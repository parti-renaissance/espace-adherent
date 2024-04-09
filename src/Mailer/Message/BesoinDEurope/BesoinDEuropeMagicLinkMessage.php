<?php

namespace App\Mailer\Message\BesoinDEurope;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class BesoinDEuropeMagicLinkMessage extends AbstractBesoinDEuropeMessage
{
    public static function create(Adherent $adherent, string $url): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '',
            [],
            ['magic_link' => $url],
        );
    }
}
