<?php

namespace App\Committee;

use App\Entity\Administrator;
use App\Entity\Committee;
use App\Validator\MergeableCommittees;
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

    /**
     * @var Administrator
     */
    private $mergedBy;

    public function __construct(Administrator $administrator)
    {
        $this->mergedBy = $administrator;
    }

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

    public function getMergedBy(): Administrator
    {
        return $this->mergedBy;
    }
}
