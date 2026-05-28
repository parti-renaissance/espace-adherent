<?php

declare(strict_types=1);

namespace App\RateLimiter;

/**
 * Pure exponential backoff curve. Given an attempt count and the timestamp of the
 * last attempt, decides whether the next call should be throttled.
 *
 * Delay formula: min($maxDelaySeconds, $baseDelaySeconds * ($multiplier ** ($count - 1))).
 * Symfony's RateLimiter has no native multiplier policy; this policy fills the gap.
 */
class ExponentialBackoffPolicy
{
    public function __construct(
        public readonly int $baseDelaySeconds,
        public readonly int $maxDelaySeconds,
        public readonly int $multiplier = 2,
    ) {
        if ($baseDelaySeconds < 1 || $maxDelaySeconds < $baseDelaySeconds || $multiplier < 1) {
            throw new \InvalidArgumentException('Invalid backoff parameters.');
        }
    }

    public function isThrottled(int $attemptCount, \DateTimeInterface $lastAttemptAt): bool
    {
        if ($attemptCount < 1) {
            return false;
        }

        $delay = min($this->maxDelaySeconds, $this->baseDelaySeconds * ($this->multiplier ** ($attemptCount - 1)));

        return time() < $lastAttemptAt->getTimestamp() + $delay;
    }
}
