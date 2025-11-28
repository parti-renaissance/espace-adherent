<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\Referral;

use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

class ReferralAdhesionCreatedMessage extends AbstractRenaissanceMessage
{
    public static function create(
        string $referrerFirstName,
        string $referredEmail,
        string $referredFirstName,
        string $adhesionLink,
        string $reportLink,
    ): self {
        return new self(
            Uuid::uuid4(),
            $referredEmail,
            $referredFirstName,
            'Nouveau parrainage',
            [],
            [
                'referrer_first_name' => self::escape($referrerFirstName),
                'referred_first_name' => self::escape($referredFirstName),
                'adhesion_link' => $adhesionLink,
                'report_link' => $reportLink,
            ]
        );
    }
}
