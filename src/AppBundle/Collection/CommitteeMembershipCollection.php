<?php

namespace AppBundle\Collection;

use AppBundle\Entity\CommitteeMembership;
use Doctrine\Common\Collections\ArrayCollection;

class CommitteeMembershipCollection extends ArrayCollection
{
    public function getAdherentUuids(): array
    {
        return array_map(
            function (CommitteeMembership $membership) {
                return (string) $membership->getAdherentUuid();
            },
            $this->getValues()
        );
    }

    public function getCommitteeUuids(): array
    {
        return array_map(
            function (CommitteeMembership $membership) {
                return (string) $membership->getCommitteeUuid();
            },
            $this->getValues()
        );
    }

    public function getCommitteeHostMemberships(): self
    {
        return $this->filter(function (CommitteeMembership $membership) {
            return $membership->isHostMember();
        });
    }

    public function getCommitteeFollowerMemberships(): self
    {
        return $this->filter(function (CommitteeMembership $membership) {
            return $membership->isFollower();
        });
    }
}
