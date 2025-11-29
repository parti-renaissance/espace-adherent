<?php

declare(strict_types=1);

namespace App\NationalEvent\Event;

use App\Entity\NationalEvent\EventInscription;

abstract class AbstractNationalEventInscriptionEvent implements NationalEventInscriptionEventInterface
{
    public function __construct(public readonly EventInscription $eventInscription)
    {
    }

    public function getEventInscription(): EventInscription
    {
        return $this->eventInscription;
    }
}
