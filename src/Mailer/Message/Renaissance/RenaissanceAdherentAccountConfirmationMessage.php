<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceAdherentAccountConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(
        Adherent $adherent,
        string $profileLink,
        string $donationLink,
        string $committeeLink,
    ): self {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Bienvenue chez Renaissance !',
            [],
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
                'profile_link' => $profileLink,
                'donation_link' => $donationLink,
                'committee_link' => $committeeLink,
            ]
        );
    }
}
