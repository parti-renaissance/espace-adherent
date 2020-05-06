<?php

namespace App\Collection;

use App\Entity\Committee;
use Doctrine\Common\Collections\ArrayCollection;

final class CommitteeCollection extends ArrayCollection
{
    public function getOrderedCommittees(CommitteeMembershipCollection $memberships): self
    {
        $uuids = $memberships->getCommitteeSupervisorMemberships()->getCommitteeUuids();
        $supervisedCommittees = $this->filter(function (Committee $committee) use ($uuids) {
            return \in_array((string) $committee->getUuid(), $uuids);
        });

        $uuids = $memberships->getCommitteeHostMemberships(CommitteeMembershipCollection::EXCLUDE_SUPERVISORS)->getCommitteeUuids();
        $hostedCommittees = $this->filter(function (Committee $committee) use ($uuids) {
            return \in_array((string) $committee->getUuid(), $uuids);
        });

        $uuids = $memberships->getCommitteeFollowerMembershipsNotWaitingForApproval()->getCommitteeUuids();
        $followedCommittees = $this->filter(function (Committee $committee) use ($uuids) {
            return \in_array((string) $committee->getUuid(), $uuids);
        });

        $uuids = $memberships->getCommitteeMembershipsInWaitingForApproval()->getCommitteeUuids();
        $waitingForApprovalCommittees = $this->filter(function (Committee $committee) use ($uuids) {
            return \in_array((string) $committee->getUuid(), $uuids);
        });

        return new static(array_merge(
            $supervisedCommittees->toArray(),
            $hostedCommittees->toArray(),
            $followedCommittees->toArray(),
            $waitingForApprovalCommittees->toArray()
        ));
    }
}
