<?php

declare(strict_types=1);

namespace App\Entity\Reporting;

use MyCLabs\Enum\Enum;

/**
 * @method static EmailSubscriptionHistoryAction SUBSCRIBE()
 * @method static EmailSubscriptionHistoryAction UNSUBSCRIBE()
 */
class EmailSubscriptionHistoryAction extends Enum
{
    public const SUBSCRIBE = 'subscribe';
    public const UNSUBSCRIBE = 'unsubscribe';
}
