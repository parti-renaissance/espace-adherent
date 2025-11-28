<?php

declare(strict_types=1);

namespace App\Committee\Event;

use App\Entity\CommitteeMembership;

interface CommitteeMembershipEventInterface
{
    public function getCommitteeMembership(): CommitteeMembership;
}
