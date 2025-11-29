<?php

declare(strict_types=1);

namespace App\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use Symfony\Contracts\EventDispatcher\Event;

class MessageEvent extends Event
{
    private $message;

    public function __construct(AdherentMessageInterface $message)
    {
        $this->message = $message;
    }

    public function getMessage(): AdherentMessageInterface
    {
        return $this->message;
    }
}
