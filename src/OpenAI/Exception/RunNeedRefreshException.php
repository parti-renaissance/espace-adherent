<?php

namespace App\OpenAI\Exception;

use App\Sentry\SentryIgnoredExceptionInterface;

class RunNeedRefreshException extends \Exception implements SentryIgnoredExceptionInterface
{
}
