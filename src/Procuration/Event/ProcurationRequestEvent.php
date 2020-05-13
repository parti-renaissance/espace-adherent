<?php

namespace App\Procuration\Event;

use App\Entity\Adherent;
use App\Entity\ProcurationRequest;
use Symfony\Component\EventDispatcher\Event;

class ProcurationRequestEvent extends Event
{
    private $request;
    private $notify;
    private $referent;

    public function __construct(ProcurationRequest $request, bool $notify = false, Adherent $referent = null)
    {
        $this->request = $request;
        $this->notify = $notify;
        $this->referent = $referent;
    }

    public function getRequest(): ProcurationRequest
    {
        return $this->request;
    }

    public function getReferent(): ?Adherent
    {
        return $this->referent;
    }

    public function notify(): bool
    {
        return $this->notify;
    }
}
