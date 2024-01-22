<?php

namespace App\Chatbot\Exception;

use App\Sentry\SentryIgnoredExceptionInterface;

class RunNotCompletedException extends \Exception implements SentryIgnoredExceptionInterface
{
}
