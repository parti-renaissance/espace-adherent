<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Donation;

final class DonationMessage extends MailjetMessage
{
    public static function createFromDonation(Donation $donation): self
    {
        return new static(
            $donation->getUuid(),
            '54677',
            $donation->getEmailAddress(),
            $donation->getFullName(),
            'Merci pour votre engagement',
            [
                'target_firstname' => $donation->getFirstName(),
                'year' => (int) $donation->getDonatedAt()->format('Y') + 1,
            ]
        );
    }
}
