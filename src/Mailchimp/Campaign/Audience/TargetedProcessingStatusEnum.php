<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

enum TargetedProcessingStatusEnum: string
{
    case Pending = 'pending';
    case Added = 'added';
    case Refused = 'refused';
    case Errored = 'errored';
}
