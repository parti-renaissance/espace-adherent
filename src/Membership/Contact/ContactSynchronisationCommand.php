<?php

namespace App\Membership\Contact;

use App\Messenger\Message\AbstractUuidAsynchronousMessage;
use App\SendInBlue\SynchronizeMessageInterface;

class ContactSynchronisationCommand extends AbstractUuidAsynchronousMessage implements SynchronizeMessageInterface
{
}
