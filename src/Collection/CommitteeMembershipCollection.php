<?php

namespace App\Collection;

use App\Entity\CommitteeMembership;
use Doctrine\Common\Collections\ArrayCollection;

class CommitteeMembershipCollection extends ArrayCollection
{
    public const INCLUDE_SUPERVISORS = 1;
    public const EXCLUDE_SUPERVISORS = 2;

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
                return $membership->getCommittee()->getUuidAsString();
            },
            $this->getValues()
        );
    }

    public function countCommitteeHostMemberships(): int
    {
        return \count($this->filter(function (CommitteeMembership $membership) {
            return $membership->canHostCommittee();
        }));
    }

    public function countCommitteeSupervisorMemberships(): int
    {
        return $this->getCommitteeSupervisorMemberships()->count();
    }

    public function getCommitteeHostMemberships(int $withSupervisors = self::INCLUDE_SUPERVISORS): self
    {
        if (self::EXCLUDE_SUPERVISORS === $withSupervisors) {
            return $this->filter(function (CommitteeMembership $membership) {
                return $membership->isHostMember();
            });
        }

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

    public function getCommitteeFollowerMembershipsNotWaitingForApproval(): self
    {
        return $this->filter(function (CommitteeMembership $membership) {
            return $membership->isFollower() && !$membership->getCommittee()->isWaitingForApproval();
        });
    }

    public function getCommitteeSupervisorMemberships(): self
    {
        return $this->filter(function (CommitteeMembership $membership) {
            return $membership->isSupervisor();
        });
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getMembershipsForApprovedCommittees(): array
    {
        return array_values(
            $this->filter(function (CommitteeMembership $membership) {
                return $membership->getCommittee()->isApproved();
            })->toArray()
        );
    }

    public function getCommitteeMembershipsInWaitingForApproval(): self
    {
        return $this->filter(function (CommitteeMembership $membership) {
            return $membership->getCommittee()->isWaitingForApproval() && !$membership->isHostMember();
        });
    }

    public function merge(self $other): void
    {
        foreach ($other as $element) {
            if (!$this->contains($element)) {
                $this->add($element);
            }
        }
    }

    public function getVotingCommitteeMembership(): ?CommitteeMembership
    {
        foreach ($this as $membership) {
            if ($membership->isVotingCommittee()) {
                return $membership;
            }
        }

        return null;
    }

    public function getCommitteeCandidacyMembership(): ?CommitteeMembership
    {
        /** @var CommitteeMembership $membership */
        foreach ($this as $membership) {
            if ($membership->hasActiveCommitteeCandidacy()) {
                return $membership;
            }
        }

        return null;
    }
}
