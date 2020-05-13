<?php

namespace App\Mailchimp\Exception;

use Symfony\Component\Messenger\Transport\AmqpExt\Exception\RejectMessageExceptionInterface;

class StaticSegmentIdMissingException extends \InvalidArgumentException implements RejectMessageExceptionInterface
{
}
