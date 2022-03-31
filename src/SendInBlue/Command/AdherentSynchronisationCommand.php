<?php

namespace App\SendInBlue\Command;

use App\Messenger\Message\AbstractUuidAsynchronousMessage;
use App\SendInBlue\SynchronizeMessageInterface;

class AdherentSynchronisationCommand extends AbstractUuidAsynchronousMessage implements SynchronizeMessageInterface
{
}
