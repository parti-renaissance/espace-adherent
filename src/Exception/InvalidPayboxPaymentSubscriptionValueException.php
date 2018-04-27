<?php

namespace AppBundle\Exception;

class InvalidPayboxPaymentSubscriptionValueException extends \InvalidArgumentException
{
    public function __construct(int $duration, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Duration value "%d" not supported.', $duration), $code, $previous);
    }
}
