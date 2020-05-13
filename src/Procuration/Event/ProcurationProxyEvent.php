<?php

namespace App\Procuration\Event;

use App\Entity\ProcurationProxy;
use Symfony\Component\EventDispatcher\Event;

class ProcurationProxyEvent extends Event
{
    private $proxy;

    public function __construct(ProcurationProxy $proxy)
    {
        $this->proxy = $proxy;
    }

    public function getProxy(): ProcurationProxy
    {
        return $this->proxy;
    }
}
