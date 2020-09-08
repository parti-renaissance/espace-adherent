<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentMembershipReminderMessage extends Message
{
    public static function create(Adherent $adherent, string $donationUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '[Adhérent] Zéro cotisation, donnez si vous le pouvez !',
            [],
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'donation_url' => $donationUrl,
            ]
        );
    }
}
