<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Transaction;

final class DonationMessage extends Message
{
    public static function create(Transaction $transaction): self
    {
        return new self(
            $transaction->getDonationUuid(),
            $transaction->getEmailAddress(),
            $transaction->getFullName(),
            [],
            ['recipient_first_name' => self::escape($transaction->getFirstName())]
        );
    }
}
