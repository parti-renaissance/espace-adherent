<?php

namespace AppBundle\Collection;

use AppBundle\Entity\GroupMembership;
use Doctrine\Common\Collections\ArrayCollection;

class GroupMembershipCollection extends ArrayCollection
{
    public function getAdherentUuids(): array
    {
        return array_map(
            function (GroupMembership $membership) {
                return (string) $membership->getAdherentUuid();
            },
            $this->getValues()
        );
    }

    public function getGroupUuids(): array
    {
        return array_map(
            function (GroupMembership $membership) {
                return (string) $membership->getGroupUuid();
            },
            $this->getValues()
        );
    }

    public function countGroupAdministratorMemberships(): int
    {
        return count($this->filter(function (GroupMembership $membership) {
            return $membership->canAdministrateGroup();
        }));
    }

    public function countGroupSupervisorMemberships(): int
    {
        return $this->getGroupSupervisorMemberships()->count();
    }

    public function getGroupAdministratorMemberships(): self
    {
        $groups = $this->filter(function (GroupMembership $membership) {
            return $membership->isAdministrator();
        });

        return $groups;
    }

    public function getGroupFollowerMemberships(): self
    {
        return $this->filter(function (GroupMembership $membership) {
            return $membership->isFollower();
        });
    }

    public function getGroupSupervisorMemberships(): self
    {
        return $this->filter(function (GroupMembership $membership) {
            return $membership->isAdministrator();
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
}
