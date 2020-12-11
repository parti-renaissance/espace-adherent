<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

class AlwaysRetryStrategy implements RetryStrategyInterface
{
    private const DELAY = 1000; // 1 sec

    public function isRetryable(Envelope $message): bool
    {
        return true;
    }

    public function getWaitingTime(Envelope $message): int
    {
        $retries = RedeliveryStamp::getRetryCountFromEnvelope($message);

        return self::DELAY * $retries;
    }
}
