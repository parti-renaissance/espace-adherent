<?php

namespace App\Procuration\V2\Event;

use App\Entity\ProcurationV2\Request;

class RequestEvent
{
    public function __construct(public readonly Request $request)
    {
    }
}
