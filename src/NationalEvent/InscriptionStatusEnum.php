<?php

namespace App\NationalEvent;

use MyCLabs\Enum\Enum;

class InscriptionStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const INCONCLUSIVE = 'inconclusive';
    public const REFUSED = 'refused';
    public const DUPLICATE = 'duplicate';

    public const STATUSES = [
        self::PENDING,
        self::ACCEPTED,
        self::INCONCLUSIVE,
        self::REFUSED,
        self::DUPLICATE,
    ];

    public const APPROVED_STATUSES = [
        self::ACCEPTED,
        self::INCONCLUSIVE,
    ];
}
