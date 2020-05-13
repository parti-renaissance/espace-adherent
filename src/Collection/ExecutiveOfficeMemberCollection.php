<?php

namespace App\Collection;

use App\Entity\Biography\ExecutiveOfficeMember;
use Doctrine\Common\Collections\ArrayCollection;

class ExecutiveOfficeMemberCollection extends ArrayCollection
{
    public function getExecutiveOfficeMembers(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return !$executiveOfficeMember->isExecutiveOfficer() && !$executiveOfficeMember->isDeputyGeneralDelegate();
        });
    }

    public function getExecutiveOfficer(): ?ExecutiveOfficeMember
    {
        $collection = $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return $executiveOfficeMember->isExecutiveOfficer();
        });

        return !$collection->isEmpty() ? $collection->first() : null;
    }

    public function getDeputyGeneralDelegate(): ?ExecutiveOfficeMember
    {
        $collection = $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return $executiveOfficeMember->isDeputyGeneralDelegate();
        });

        return !$collection->isEmpty() ? $collection->first() : null;
    }
}
