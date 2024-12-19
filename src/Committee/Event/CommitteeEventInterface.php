<?php

namespace App\Committee\Event;

use App\Entity\CommitteeMembership;

interface CommitteeEventInterface
{
    public function getCommitteeMembership(): CommitteeMembership;
}
