<?php

namespace App\NationalEvent\Event;

use App\Entity\NationalEvent\EventInscription;

interface NationalEventInscriptionEventInterface
{
    public function getEventInscription(): EventInscription;
}
