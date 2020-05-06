<?php

namespace App\Exception;

use App\Entity\EventRegistration;

class EventRegistrationException extends \RuntimeException
{
    private $registration;

    public function __construct($message = '', EventRegistration $registration = null, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->registration = $registration;
    }

    public function getEventRegistration(): ?EventRegistration
    {
        return $this->registration;
    }
}
