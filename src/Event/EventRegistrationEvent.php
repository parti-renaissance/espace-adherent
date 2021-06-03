<?php

namespace App\Event;

use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Entity\Event\EventRegistration;
use Symfony\Contracts\EventDispatcher\Event;

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

    public function isForCoalitionsEvent(): bool
    {
        $event = $this->registration->getEvent();

        return $event instanceof CoalitionEvent || $event instanceof CauseEvent;
    }
}
