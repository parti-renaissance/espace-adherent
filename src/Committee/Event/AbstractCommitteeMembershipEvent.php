<?php

namespace App\Committee\Event;

use App\Entity\CommitteeMembership;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractCommitteeMembershipEvent extends Event implements CommitteeEventInterface
{
    public function __construct(private readonly CommitteeMembership $committeeMembership)
    {
    }

    public function getCommitteeMembership(): CommitteeMembership
    {
        return $this->committeeMembership;
    }
}
