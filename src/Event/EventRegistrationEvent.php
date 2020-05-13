<?php

namespace App\Event;

use App\Entity\EventRegistration;
use Symfony\Component\EventDispatcher\Event;

class EventRegistrationEvent extends Event
{
    private $registration;
    private $slug;
    private $sendMail;

    public function __construct(EventRegistration $registration, string $slug, bool $sendMail)
    {
        $this->registration = $registration;
        $this->slug = $slug;
        $this->sendMail = $sendMail;
    }

    public function getRegistration(): EventRegistration
    {
        return $this->registration;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getSendMail(): bool
    {
        return $this->sendMail;
    }
}
