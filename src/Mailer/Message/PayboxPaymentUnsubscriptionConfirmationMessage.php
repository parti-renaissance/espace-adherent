<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class PayboxPaymentUnsubscriptionConfirmationMessage extends Message
{
    public static function create(Adherent $adherent): self
    {
        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            [],
            [
                'recipient_first_name' => self::escape($adherent->getFirstName()),
            ]
        );
    }
}
