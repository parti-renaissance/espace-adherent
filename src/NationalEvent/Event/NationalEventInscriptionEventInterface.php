<?php

declare(strict_types=1);

namespace App\NationalEvent\Event;

use App\Entity\NationalEvent\EventInscription;

interface NationalEventInscriptionEventInterface
{
    public function getEventInscription(): EventInscription;
}
