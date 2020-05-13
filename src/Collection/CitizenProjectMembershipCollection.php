<?php

namespace App\Collection;

use App\Entity\CitizenProjectMembership;
use Doctrine\Common\Collections\ArrayCollection;

class CitizenProjectMembershipCollection extends ArrayCollection
{
    public function getAdherentUuids(): array
    {
        return array_map(
            function (CitizenProjectMembership $membership) {
                return (string) $membership->getAdherentUuid();
            },
            $this->getValues()
        );
    }

    public function getCitizenProjectUuids(): array
    {
        return array_map(
            function (CitizenProjectMembership $membership) {
                return (string) $membership->getCitizenProjectUuid();
            },
            $this->getValues()
        );
    }

    public function countCitizenProjectAdministratorMemberships(): int
    {
        return \count($this->filter(function (CitizenProjectMembership $membership) {
            return $membership->canAdministrateCitizenProject();
        }));
    }

    public function getCitizenProjectAdministratorMemberships(): self
    {
        $citizenProjects = $this->filter(function (CitizenProjectMembership $membership) {
            return $membership->isAdministrator();
        });

        return $citizenProjects;
    }

    public function getCitizenProjectFollowerMemberships(): self
    {
        return $this->filter(function (CitizenProjectMembership $membership) {
            return $membership->isFollower();
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

    public function filterRefusedProjects(): self
    {
        return $this->filter(function (CitizenProjectMembership $membership) {
            return !$membership->getCitizenProject()->isRefused();
        });
    }
}
