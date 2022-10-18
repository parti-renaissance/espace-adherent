<?php

namespace App\Collection;

use App\Entity\Biography\ExecutiveOfficeMember;
use App\Entity\Biography\ExecutiveOfficeRoleEnum;
use Doctrine\Common\Collections\ArrayCollection;

class ExecutiveOfficeMemberCollection extends ArrayCollection
{
    public function getExecutiveOfficeMembers(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return ExecutiveOfficeRoleEnum::EXECUTIVE_OFFICE_MEMBER === $executiveOfficeMember->getRole();
        });
    }

    public function getPresident(): ?ExecutiveOfficeMember
    {
        $collection = $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return ExecutiveOfficeRoleEnum::PRESIDENT === $executiveOfficeMember->getRole();
        });

        return !$collection->isEmpty() ? $collection->first() : null;
    }

    public function getExecutiveOfficer(): ?ExecutiveOfficeMember
    {
        $collection = $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return ExecutiveOfficeRoleEnum::EXECUTIVE_OFFICER === $executiveOfficeMember->getRole();
        });

        return !$collection->isEmpty() ? $collection->first() : null;
    }

    public function getDeputyGeneralDelegate(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return ExecutiveOfficeRoleEnum::DEPUTY_GENERAL_DELEGATE === $executiveOfficeMember->getRole();
        });
    }

    public function getSpecialGroup(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return \in_array($executiveOfficeMember->getRole(), [ExecutiveOfficeRoleEnum::PRESIDENT, ExecutiveOfficeRoleEnum::EXECUTIVE_OFFICER], true);
        });
    }

    public function getRegularGroup(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return ExecutiveOfficeRoleEnum::DEPUTY_GENERAL_DELEGATE === $executiveOfficeMember->getRole();
        });
    }

    public function getFonctionnalDelegateMembers(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return ExecutiveOfficeRoleEnum::FUNCTIONAL_DELEGATE === $executiveOfficeMember->getRole();
        });
    }

    public function getMembersByRight(): self
    {
        return $this->filter(function (ExecutiveOfficeMember $executiveOfficeMember) {
            return ExecutiveOfficeRoleEnum::MEMBER_BY_RIGHT === $executiveOfficeMember->getRole();
        });
    }
}
