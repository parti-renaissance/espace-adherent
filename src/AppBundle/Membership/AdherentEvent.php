<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use Symfony\Component\EventDispatcher\Event;

class AdherentEvent extends Event
{
    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
}
