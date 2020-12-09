<?php

namespace App\Mailchimp\Exception;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class StaticSegmentIdMissingException extends UnrecoverableMessageHandlingException
{
}
