<?php

namespace App\TerritorialCouncil\Event;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Symfony\Component\EventDispatcher\Event;

class TerritorialCouncilEvent extends Event
{
    private $territorialCouncil;

    public function __construct(TerritorialCouncil $territorialCouncil)
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }
}
