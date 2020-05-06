<?php

namespace App\Entity\Reporting;

use MyCLabs\Enum\Enum;

/**
 * @method static SUBSCRIBE()
 * @method static UNSUBSCRIBE()
 */
class EmailSubscriptionHistoryAction extends Enum
{
    public const SUBSCRIBE = 'subscribe';
    public const UNSUBSCRIBE = 'unsubscribe';
}
