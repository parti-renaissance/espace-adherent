<?php

namespace App\Exception;

class InvalidPayboxPaymentSubscriptionValueException extends \InvalidArgumentException
{
    public function __construct(int $duration, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Duration value "%d" not supported.', $duration), 0, $previous);
    }
}
