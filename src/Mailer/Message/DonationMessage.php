<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Donation;

final class DonationMessage extends Message
{
    public static function createFromDonation(Donation $donation): self
    {
        return new self(
            $donation->getUuid(),
            $donation->getEmailAddress(),
            $donation->getFullName(),
            [],
            static::getRecipientVars($donation)
        );
    }

    private static function getRecipientVars(Donation $donation): array
    {
        return [
            'target_firstname' => self::escape($donation->getFirstName()),
            'year' => (int) $donation->getDonatedAt()->format('Y') + 1,
        ];
    }
}
