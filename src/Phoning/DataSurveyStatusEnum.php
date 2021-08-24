<?php

namespace App\Phoning;

use MyCLabs\Enum\Enum;

class DataSurveyStatusEnum extends Enum
{
    public const SEND = 'send';
    public const TO_UNSUBSCRIBE = 'to-unsubscribe';
    public const TO_UNJOIN = 'to-unjoin';
    public const NOT_RESPONd = 'not-respond';
    public const TO_REMIND = 'to-remind';
    public const FAILED = 'failed';
    public const INTERRUPTED_DONT_REMIND = 'interrupted-dont-remind';
    public const INTERRUPTED = 'interrupted';
    public const COMPLETED = 'completed';
}
