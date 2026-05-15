<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\Referral;

use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Symfony\Component\Uid\Uuid;

class ReferralAdhesionFinishedMessage extends AbstractRenaissanceMessage
{
    public static function create(
        string $referrerFirstName,
        string $referredEmail,
        string $referredFirstName,
    ): self {
        return new self(
            Uuid::v4(),
            $referredEmail,
            $referredFirstName,
            'Parrainage terminé',
            [],
            [
                'referrer_first_name' => self::escape($referrerFirstName),
                'referred_first_name' => self::escape($referredFirstName),
            ]
        );
    }
}
