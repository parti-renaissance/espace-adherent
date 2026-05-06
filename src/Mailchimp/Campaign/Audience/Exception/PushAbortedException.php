<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Exception;

use App\Mailchimp\Campaign\Audience\PushResult;

class PushAbortedException extends \RuntimeException
{
    public function __construct(public readonly PushResult $partialResult, string $reason)
    {
        parent::__construct($reason);
    }
}
