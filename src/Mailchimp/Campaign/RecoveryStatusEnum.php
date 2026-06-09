<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

enum RecoveryStatusEnum: string
{
    case Attempted = 'attempted';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Aborted = 'aborted';
}
