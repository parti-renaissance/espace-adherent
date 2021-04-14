<?php

namespace App\Coalition;

use App\Entity\Coalition\Cause;
use Symfony\Contracts\EventDispatcher\Event;

class CauseFollowerChangeEvent extends Event
{
    private $cause;

    public function __construct(Cause $cause = null)
    {
        $this->cause = $cause;
    }

    public function getCause()
    {
        return $this->cause;
    }
}
