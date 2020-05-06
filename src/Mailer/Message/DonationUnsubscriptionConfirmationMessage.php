<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Donation;
use Ramsey\Uuid\Uuid;

final class DonationUnsubscriptionConfirmationMessage extends Message
{
    public static function create(Adherent $adherent, Donation $donation): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre don mensuel a bien été annulé.',
            ['target_firstname' => self::escape($adherent->getFirstName())]
        );
    }
}
