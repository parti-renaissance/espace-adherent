<?php

namespace App\Committee\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractCommitteeMembershipEvent extends Event implements CommitteeEventInterface
{
    private $committee;
    private $adherent;

    public function __construct(Adherent $adherent, Committee $committee = null)
    {
        $this->committee = $committee;
        $this->adherent = $adherent;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
}
