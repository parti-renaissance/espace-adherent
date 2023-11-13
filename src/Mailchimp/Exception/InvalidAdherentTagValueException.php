<?php

namespace App\Mailchimp\Exception;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class InvalidAdherentTagValueException extends UnrecoverableMessageHandlingException
{
}
