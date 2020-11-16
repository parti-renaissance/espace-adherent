<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use Symfony\Component\EventDispatcher\Event;

class AbstractEvent extends Event implements ElectionNotifyEventInterface
{
    private $adherent;
    private $designation;

    public function __construct(Adherent $adherent, Designation $designation)
    {
        $this->adherent = $adherent;
        $this->designation = $designation;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getDesignation(): Designation
    {
        return $this->designation;
    }
}
