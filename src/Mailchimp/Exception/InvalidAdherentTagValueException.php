<?php

declare(strict_types=1);

namespace App\Mailchimp\Exception;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class InvalidAdherentTagValueException extends UnrecoverableMessageHandlingException
{
}
