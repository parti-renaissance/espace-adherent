<?php

namespace App\SendInBlue\Command;

use App\Messenger\Message\AbstractUuidAsynchronousMessage;
use App\SendInBlue\SynchronizeMessageInterface;

class AdherentDeleteCommand extends AbstractUuidAsynchronousMessage implements SynchronizeMessageInterface
{
}
