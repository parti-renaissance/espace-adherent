<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

enum PreparationStatusEnum: string
{
    case NotStarted = 'not_started';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Failed = 'failed';
}
