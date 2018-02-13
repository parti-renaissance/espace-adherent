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
            self::getRecipientVars($donation->getFirstName())
        );
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
