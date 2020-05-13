<?php

namespace App\Mailchimp\Exception;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use Symfony\Component\Messenger\Transport\AmqpExt\Exception\RejectMessageExceptionInterface;

class InvalidFilterException extends \InvalidArgumentException implements RejectMessageExceptionInterface
{
    public function __construct(AdherentMessageInterface $message, string $description)
    {
        parent::__construct(sprintf('%s [message id: "%s"]', $description, $message->getUuid()->toString()));
    }
}
