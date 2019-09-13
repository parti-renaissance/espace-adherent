<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Transaction;

final class DonationThanksMessage extends Message
{
    public static function createFromTransaction(Transaction $transaction): self
    {
        $donation = $transaction->getDonation();
        $donator = $donation->getDonator();

        return new self(
            $donation->getUuid(),
            $donator->getEmailAddress(),
            $donator->getFullName(),
            'Merci pour votre engagement',
            [
                'target_firstname' => self::escape($donator->getFirstName()),
                'donation_amount' => $donation->getAmountInEuros(),
            ]
        );
    }
}
