<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

enum MandrillFallbackStatusEnum: string
{
    case Attempted = 'attempted';
    case Sent = 'sent';
    case Skipped = 'skipped';
    case Aborted = 'aborted';
    case Failed = 'failed';
}
