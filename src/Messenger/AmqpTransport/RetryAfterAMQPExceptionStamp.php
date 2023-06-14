<?php

namespace App\Messenger\AmqpTransport;

use Symfony\Component\Messenger\Stamp\StampInterface;

class RetryAfterAMQPExceptionStamp implements StampInterface
{
    private int $retries = 0;

    public function increaseRetryAttempts(): void
    {
        ++$this->retries;
    }

    public function getRetryAttempts(): int
    {
        return $this->retries;
    }
}
