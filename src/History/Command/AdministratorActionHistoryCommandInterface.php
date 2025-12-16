<?php

declare(strict_types=1);

namespace App\History\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

interface AdministratorActionHistoryCommandInterface extends AsynchronousMessageInterface
{
}
