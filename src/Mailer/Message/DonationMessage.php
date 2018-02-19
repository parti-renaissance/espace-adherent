<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Transaction;

final class DonationMessage extends Message
{
    public static function create(Transaction $transaction): self
    {
        $donation = $transaction->getDonation();

        return new self(
            $donation->getUuid(),
            $donation->getEmailAddress(),
            $donation->getFullName(),
            static::getTemplateVars($donation)
        );
    }

    private static function getTemplateVars(Donation $donation): array
    {
        return [
            'first_name' => self::escape($donation->getFirstName()),
            'year' => (int) $transaction->getPayboxDateTime()->format('Y') + 1,
        ];
    }
}
