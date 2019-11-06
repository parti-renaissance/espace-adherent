<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Transaction;

final class DonationMessage extends Message
{
    public static function createFromTransaction(Transaction $transaction): self
    {
        $donation = $transaction->getDonation();
        $donator = $donation->getDonator();

        return new self(
            $donation->getUuid(),
            '54677',
            $donator->getEmailAddress(),
            $donator->getFullName(),
            'Merci pour votre engagement',
            [
                'target_firstname' => self::escape($donator->getFirstName()),
                'year' => (int) $transaction->getPayboxDateTime()->format('Y') + 1,
                'donation_amount' => $transaction->getDonation()->getAmountInEuros(),
            ]
        );
    }
}
