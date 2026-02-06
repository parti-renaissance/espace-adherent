<?php

declare(strict_types=1);

namespace App\Procuration\Event;

use App\Entity\Procuration\AbstractProcuration;

class ProcurationEvent
{
    public function __construct(public readonly AbstractProcuration $procuration)
    {
    }
}
