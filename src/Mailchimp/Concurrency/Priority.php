<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency;

enum Priority
{
    /** Live work (mailchimp_sync, mailchimp_campaign): full slot pool. */
    case High;

    /** Bulk work (mailchimp_batch): capped pool, leaves headroom for High. */
    case Low;
}
