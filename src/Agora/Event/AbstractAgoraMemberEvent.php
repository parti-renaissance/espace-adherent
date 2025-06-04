<?php

namespace App\Agora\Event;

use App\Entity\AgoraMembership;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractAgoraMemberEvent extends Event
{
    public function __construct(public readonly AgoraMembership $agoraMembership)
    {
    }
}
