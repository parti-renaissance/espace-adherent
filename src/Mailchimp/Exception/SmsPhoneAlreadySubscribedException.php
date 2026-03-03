<?php

declare(strict_types=1);

namespace App\Mailchimp\Exception;

/**
 * Thrown when Mailchimp rejects a phone number because it is already
 * subscribed to another contact in the SMS program.
 */
final class SmsPhoneAlreadySubscribedException extends \Exception
{
    public function __construct(
        public readonly string $phone,
        string $message = 'Phone number already subscribed to another contact',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
