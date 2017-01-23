<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use Symfony\Component\EventDispatcher\Event;

class CommitteeEvent extends Event
{
    private $committee;

    public function __construct(Committee $committee)
    {
        $this->committee = $committee;
    }

    public function getCommittee()
    {
        return $this->committee;
    }
}
