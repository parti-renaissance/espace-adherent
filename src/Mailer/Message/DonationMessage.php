<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Donation;

final class DonationMessage extends Message
{
    public static function createFromDonation(Donation $donation): self
    {
        return new self(
            $donation->getUuid(),
            '54677',
            $donation->getEmailAddress(),
            $donation->getFullName(),
            'Merci pour votre engagement',
            [
                'target_firstname' => self::escape($donation->getFirstName()),
                'year' => (int) $donation->getDonatedAt()->format('Y') + 1,
            ]
        );
    }
}
