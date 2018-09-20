<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use AppBundle\Validator\MergeableCommittees;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MergeableCommittees
 */
class CommitteeMergeCommand
{
    /**
     * @var Committee|null
     *
     * @Assert\NotNull
     */
    private $sourceCommittee;

    /**
     * @var Committee|null
     *
     * @Assert\NotNull
     */
    private $destinationCommittee;

    public function getSourceCommittee(): ?Committee
    {
        return $this->sourceCommittee;
    }

    public function setSourceCommittee(Committee $sourceCommittee): void
    {
        $this->sourceCommittee = $sourceCommittee;
    }

    public function getDestinationCommittee(): ?Committee
    {
        return $this->destinationCommittee;
    }

    public function setDestinationCommittee(Committee $destinationCommittee): void
    {
        $this->destinationCommittee = $destinationCommittee;
    }
}
