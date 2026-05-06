<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency;

/**
 * Returned by MailchimpSemaphore when Redis is unreachable (fail-open behavior).
 */
class NullSlot implements MailchimpSlot
{
    public function release(): void
    {
        // no-op: nothing was acquired in fail-open mode
    }
}
