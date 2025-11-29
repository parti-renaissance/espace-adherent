<?php

declare(strict_types=1);

namespace App\Adherent\Tag\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class AsyncRefreshAdherentTagCommand extends RefreshAdherentTagCommand implements AsynchronousMessageInterface
{
}
