<?php

namespace AppBundle\Mailchimp\Exception;

use Symfony\Component\Messenger\Transport\AmqpExt\Exception\RejectMessageExceptionInterface;

class InvalidFilterException extends \InvalidArgumentException implements RejectMessageExceptionInterface
{
}
