<?php

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceReAdhesionConfirmationMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(
        Adherent $adherent,
        string $profileLink,
        string $donationLink,
        string $committeeLink
    ): self {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmation de votre cotisation à Renaissance !',
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
                'year' => $adherent->getLastMembershipDonation()?->format('Y'),
                'profile_link' => $profileLink,
                'donation_link' => $donationLink,
                'committee_link' => $committeeLink,
            ]
        );
    }
}
