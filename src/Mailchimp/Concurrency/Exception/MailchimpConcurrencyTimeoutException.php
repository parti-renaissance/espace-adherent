<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency\Exception;

use App\Mailchimp\Concurrency\MailchimpSemaphore;
use App\Sentry\SentryIgnoredExceptionInterface;

/**
 * Thrown when no Mailchimp slot becomes available within the acquire timeout.
 * Implements SentryIgnoredExceptionInterface because this is back-pressure, not
 * a bug: Messenger retries the message and the load eventually drains.
 */
class MailchimpConcurrencyTimeoutException extends \RuntimeException implements SentryIgnoredExceptionInterface
{
    public function __construct(int $timeoutMs)
    {
        parent::__construct(\sprintf(
            'Mailchimp concurrency semaphore timeout after %dms — all %d slots are held.',
            $timeoutMs,
            MailchimpSemaphore::SLOT_COUNT
        ));
    }
}
