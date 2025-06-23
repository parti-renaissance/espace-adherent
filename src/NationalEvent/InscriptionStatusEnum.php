<?php

namespace App\NationalEvent;

use MyCLabs\Enum\Enum;

class InscriptionStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const WAITING_PAYMENT = 'waiting_payment';
    public const PAYMENT_CONFIRMED = 'payment_confirmed';
    public const ACCEPTED = 'accepted';
    public const INCONCLUSIVE = 'inconclusive';
    public const REFUSED = 'refused';
    public const DUPLICATE = 'duplicate';
    public const CANCELED = 'canceled';

    public const STATUSES = [
        self::PENDING,
        self::WAITING_PAYMENT,
        self::PAYMENT_CONFIRMED,
        self::ACCEPTED,
        self::INCONCLUSIVE,
        self::REFUSED,
        self::DUPLICATE,
        self::CANCELED,
    ];

    public const APPROVED_STATUSES = [
        self::ACCEPTED,
        self::INCONCLUSIVE,
    ];
}
