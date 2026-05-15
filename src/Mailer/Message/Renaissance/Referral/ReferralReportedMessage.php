<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\Referral;

use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Symfony\Component\Uid\Uuid;

class ReferralReportedMessage extends AbstractRenaissanceMessage
{
    public static function create(
        string $referrerEmail,
        string $referrerFirstName,
        string $referredFirstName,
    ): self {
        return new self(
            Uuid::v4(),
            $referrerEmail,
            $referrerFirstName,
            'Parrainage signalé',
            [],
            [
                'referrer_first_name' => self::escape($referrerFirstName),
                'referred_first_name' => self::escape($referredFirstName),
            ]
        );
    }
}
