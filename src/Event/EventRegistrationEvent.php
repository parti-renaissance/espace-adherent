<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Event\EventRegistration;
use Symfony\Contracts\EventDispatcher\Event;

class EventRegistrationEvent extends Event
{
    public function __construct(
        private readonly EventRegistration $registration,
        private readonly bool $sendMail,
    ) {
    }

    public function getRegistration(): EventRegistration
    {
        return $this->registration;
    }

    public function getSendMail(): bool
    {
        return $this->sendMail;
    }
}
