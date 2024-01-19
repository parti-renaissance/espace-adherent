<?php

namespace App\Chatbot\Exception;

use App\Messenger\Exception\NeedRetryExceptionInterface;

class RunNotCompletedException extends \Exception implements NeedRetryExceptionInterface
{
}
