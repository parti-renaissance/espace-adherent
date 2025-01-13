<?php

namespace App\Committee\Event;

use App\Entity\CommitteeMembership;

interface CommitteeMembershipEventInterface
{
    public function getCommitteeMembership(): CommitteeMembership;
}
