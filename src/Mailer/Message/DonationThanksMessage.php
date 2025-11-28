<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Transaction;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;

final class DonationThanksMessage extends AbstractRenaissanceMessage
{
    public static function createFromTransaction(Transaction $transaction): Message
    {
        $donation = $transaction->getDonation();
        $donator = $donation->getDonator();

        $message = new self(
            $donation->getUuid(),
            $donator->getEmailAddress(),
            $donator->getFullName(),
            'Merci pour votre don',
            [
                'target_firstname' => self::escape($donator->getFirstName()),
                'donation_amount' => $donation->getAmountInEuros(),
            ]
        );

        return self::updateSenderInfo($message);
    }
}
