<?php

namespace AppBundle\Mailchimp\Exception;

use Symfony\Component\Messenger\Transport\AmqpExt\Exception\RejectMessageExceptionInterface;

class AdherentNotFoundException extends \Exception implements RejectMessageExceptionInterface
{
}
