<?php

namespace App\Procuration\V2\Event;

use App\Entity\ProcurationV2\AbstractProcuration;

class NewProcurationEvent
{
    public function __construct(public readonly AbstractProcuration $procuration)
    {
    }
}
