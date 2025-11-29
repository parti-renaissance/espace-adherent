<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\Referral;

use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

class ReferralReportedMessage extends AbstractRenaissanceMessage
{
    public static function create(
        string $referrerEmail,
        string $referrerFirstName,
        string $referredFirstName,
    ): self {
        return new self(
            Uuid::uuid4(),
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
