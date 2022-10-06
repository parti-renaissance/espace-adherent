<?php

namespace App\Collection;

use App\Entity\Biography\ExecutiveOfficeMember;
use Doctrine\Common\Collections\ArrayCollection;

class ExecutiveOfficeMemberCollection extends ArrayCollection
{
    public function getExecutiveOfficeMembers(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return !$executiveOfficeMember->isPresident()
                && !$executiveOfficeMember->isExecutiveOfficer()
                && !$executiveOfficeMember->isDeputyGeneralDelegate()
            ;
        });
    }

    public function getPresident(): ?ExecutiveOfficeMember
    {
        $collection = $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return $executiveOfficeMember->isPresident();
        });

        return !$collection->isEmpty() ? $collection->first() : null;
    }

    public function getExecutiveOfficer(): ?ExecutiveOfficeMember
    {
        $collection = $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return $executiveOfficeMember->isExecutiveOfficer();
        });

        return !$collection->isEmpty() ? $collection->first() : null;
    }

    public function getDeputyGeneralDelegate(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return $executiveOfficeMember->isDeputyGeneralDelegate();
        });
    }

    public function getSpecialGroup(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return $executiveOfficeMember->isPresident()
                || $executiveOfficeMember->isExecutiveOfficer()
            ;
        });
    }

    public function getRegularGroup(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return $executiveOfficeMember->isDeputyGeneralDelegate();
        });
    }
}
