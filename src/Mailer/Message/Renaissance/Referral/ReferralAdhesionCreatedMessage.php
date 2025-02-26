<?php

namespace App\Mailer\Message\Renaissance\Referral;

use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

class ReferralAdhesionCreatedMessage extends AbstractRenaissanceMessage
{
    public static function create(
        string $email,
        string $firstName,
        string $adhesionLink,
        string $reportLink,
    ): self {
        return new self(
            Uuid::uuid4(),
            $email,
            $firstName,
            'Nouveau parrainage',
            [],
            [
                'first_name' => self::escape($firstName),
                'adhesion_link' => $adhesionLink,
                'report_link' => $reportLink,
            ]
        );
    }
}
