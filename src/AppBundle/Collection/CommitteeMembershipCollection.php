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
        // Supervised committees must have top priority in the list.
        $committees = $this->filter(function (CommitteeMembership $membership) {
            return $membership->isSupervisor();
        });

        // Hosted committees must have medium priority in the list.
        $committees->merge($this->filter(function (CommitteeMembership $membership) {
            return $membership->isHostMember();
        }));

        return $committees;
    }

    public function getCommitteeFollowerMemberships(): self
    {
        return $this->filter(function (CommitteeMembership $membership) {
            return $membership->isFollower();
        });
    }

    private function merge(self $other): void
    {
        foreach ($other as $element) {
            if (!$this->contains($element)) {
                $this->add($element);
            }
        }
    }
}
