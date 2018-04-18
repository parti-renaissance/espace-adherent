<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use Ramsey\Uuid\Uuid;

class PayboxPaymentUnsubscriptionConfirmationMessage extends Message
{
    public static function create(Adherent $adherent, Donation $donation): self
    {
        return new self(
            Uuid::uuid4(),
            '366554',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre don mensuel a bien été annulé.',
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
            ]
        );
    }
}
