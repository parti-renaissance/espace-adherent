<?php

namespace AppBundle\Committee\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
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
