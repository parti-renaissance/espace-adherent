<?php

namespace App\NationalEvent\Event;

use App\Entity\NationalEvent\EventInscription;

abstract class AbstractNationalEventInscriptionEvent implements NationalEventInscriptionEventInterface
{
    public function __construct(public readonly EventInscription $eventInscription)
    {
    }
}
