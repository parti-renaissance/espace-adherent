<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Donation;

final class DonationMessage extends Message
{
    public static function create(Donation $donation): self
    {
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
            'year' => (int) $donation->getDonatedAt()->format('Y') + 1,
        ];
    }
}
