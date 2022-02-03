<?php

namespace App\Pap\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class BuildingEventAsyncCommand extends BuildingEventCommand implements AsynchronousMessageInterface
{
}
