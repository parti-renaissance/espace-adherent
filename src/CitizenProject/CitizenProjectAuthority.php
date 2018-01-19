<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Repository\CitizenProjectMembershipRepository;

class CitizenProjectAuthority
{
    private $membershipRepository;

    public function __construct(CitizenProjectMembershipRepository $membershipRepository)
    {
        $this->membershipRepository = $membershipRepository;
    }

    public function isPromotableAdministrator(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        if (!$membership = $this->getCitizenProjectMembership($adherent, $citizenProject)) {
            return false;
        }

        return $membership->isFollower();
    }

    public function isDemotableAdministrator(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        if (!$membership = $this->getCitizenProjectMembership($adherent, $citizenProject)) {
            return false;
        }

        return $membership->isAdministrator();
    }

    public function changePrivilege(Adherent $adherent, CitizenProject $citizenProject, string $privilege): void
    {
        CitizenProjectMembership::checkPrivilege($privilege);

        if (!$membership = $this->getCitizenProjectMembership($adherent, $citizenProject)) {
            return;
        }

        $membership->setPrivilege($privilege);
    }

    private function getCitizenProjectMembership(Adherent $adherent, CitizenProject $citizenProject): ?CitizenProjectMembership
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related citizen project memberships entities.
        if ($adherent->hasLoadedCitizenProjectMemberships()) {
            return $adherent->getCitizenProjectMembershipFor($citizenProject);
        }

        return $this->membershipRepository->findCitizenProjectMembership($adherent, $citizenProject->getUuid());
    }
}
