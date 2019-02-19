<?php

namespace AppBundle\AdherentMessage\Command;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;

class CreateDefaultMessageFilterCommand
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
