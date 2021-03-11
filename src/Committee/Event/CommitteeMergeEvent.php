<?php

namespace App\Committee\Event;

use App\Entity\Committee;
use Symfony\Contracts\EventDispatcher\Event;

class CommitteeMergeEvent extends Event
{
    private $sourceCommittee;
    private $destinationCommittee;

    public function __construct(Committee $sourceCommittee, Committee $destinationCommittee)
    {
        $this->sourceCommittee = $sourceCommittee;
        $this->destinationCommittee = $destinationCommittee;
    }

    public function getSourceCommittee(): Committee
    {
        return $this->sourceCommittee;
    }

    public function getDestinationCommittee(): Committee
    {
        return $this->destinationCommittee;
    }
}
