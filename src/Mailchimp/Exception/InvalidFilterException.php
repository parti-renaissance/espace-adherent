<?php

declare(strict_types=1);

namespace App\Mailchimp\Exception;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class InvalidFilterException extends UnrecoverableMessageHandlingException
{
    public function __construct(AdherentMessageInterface $message, string $description)
    {
        parent::__construct(\sprintf('%s [message id: "%s"]', $description, $message->getUuid()->toString()));
    }
}
