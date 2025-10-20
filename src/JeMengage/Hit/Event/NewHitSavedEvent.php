<?php

namespace App\JeMengage\Hit\Event;

use App\Entity\AppHit;
use Symfony\Contracts\EventDispatcher\Event;

class NewHitSavedEvent extends Event
{
    public function __construct(public readonly AppHit $hit)
    {
    }
}
