<?php

namespace App\NationalEvent;

use App\Entity\NationalEvent\EventInscription;

class NewNationalEventInscriptionEvent
{
    public function __construct(public readonly EventInscription $eventInscription)
    {
    }
}
