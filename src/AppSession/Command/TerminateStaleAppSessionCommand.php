<?php

declare(strict_types=1);

namespace App\AppSession\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class TerminateStaleAppSessionCommand implements AsynchronousMessageInterface
{
}
