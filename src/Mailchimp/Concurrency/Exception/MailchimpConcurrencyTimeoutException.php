<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency\Exception;

use App\Mailchimp\Concurrency\MailchimpSemaphore;

class MailchimpConcurrencyTimeoutException extends \RuntimeException
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
