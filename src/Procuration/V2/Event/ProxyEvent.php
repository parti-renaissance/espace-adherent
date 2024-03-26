<?php

namespace App\Procuration\V2\Event;

use App\Entity\ProcurationV2\Proxy;

class ProxyEvent
{
    public function __construct(public readonly Proxy $proxy)
    {
    }
}
