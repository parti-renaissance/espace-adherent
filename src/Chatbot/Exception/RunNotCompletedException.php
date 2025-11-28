<?php

declare(strict_types=1);

namespace App\Chatbot\Exception;

use App\Sentry\SentryIgnoredExceptionInterface;

class RunNotCompletedException extends \Exception implements SentryIgnoredExceptionInterface
{
}
