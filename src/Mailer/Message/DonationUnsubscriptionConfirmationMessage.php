<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Donation;
use Symfony\Component\Uid\Uuid;

final class DonationUnsubscriptionConfirmationMessage extends Message
{
    public static function create(Adherent $adherent, Donation $donation): self
    {
        return new self(
            Uuid::v4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre don mensuel a bien été annulé.',
            ['target_firstname' => self::escape($adherent->getFirstName())]
        );
    }
}
