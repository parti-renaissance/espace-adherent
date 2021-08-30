<?php

namespace App\Phoning;

use MyCLabs\Enum\Enum;

class CampaignHistoryStatusEnum extends Enum
{
    public const SEND = 'send';
    public const TO_UNSUBSCRIBE = 'to-unsubscribe';
    public const TO_UNJOIN = 'to-unjoin';
    public const NOT_RESPOND = 'not-respond';
    public const TO_REMIND = 'to-remind';
    public const FAILED = 'failed';
    public const INTERRUPTED_DONT_REMIND = 'interrupted-dont-remind';
    public const INTERRUPTED = 'interrupted';
    public const COMPLETED = 'completed';

    public const NOT_CALLABLE = [
        self::TO_UNJOIN,
        self::TO_UNSUBSCRIBE,
        self::FAILED,
    ];

    public const CALLABLE_LATER = [
        self::SEND,
        self::NOT_RESPOND,
        self::TO_REMIND,
        self::INTERRUPTED,
    ];
}
