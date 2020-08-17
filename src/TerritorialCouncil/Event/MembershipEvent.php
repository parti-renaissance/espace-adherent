<?php

namespace App\TerritorialCouncil\Event;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Symfony\Component\EventDispatcher\Event;

class MembershipEvent extends Event
{
    private $adherent;
    private $territorialCouncil;

    public function __construct(Adherent $adherent, TerritorialCouncil $territorialCouncil)
    {
        $this->adherent = $adherent;
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }
}
