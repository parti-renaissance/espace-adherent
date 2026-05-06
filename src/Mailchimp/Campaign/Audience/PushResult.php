<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

class PushResult
{
    /**
     * @param int          $okCount       number of chunks that returned 200/204 with at least 1 email accepted
     * @param int          $erroredCount  number of chunks that errored (HTTP 4xx/5xx, transport, or 200 with total_added=0)
     * @param int          $addedCount    emails actually added to the segment (sum of `total_added` across OK chunks)
     * @param int          $refusedCount  emails refused by Mailchimp (errors[] in 200 responses + count rejected on 0-add chunks)
     * @param list<string> $refusedEmails emails Mailchimp refused (sample, may be truncated)
     * @param list<string> $errorMessages aggregated error messages (HTTP errors, parsing, 0-add)
     */
    public function __construct(
        public int $okCount,
        public int $erroredCount,
        public int $addedCount,
        public int $refusedCount,
        public array $refusedEmails,
        public array $errorMessages,
        public float $durationSeconds,
    ) {
    }

    public function isSuccess(): bool
    {
        return 0 === $this->erroredCount;
    }
}
