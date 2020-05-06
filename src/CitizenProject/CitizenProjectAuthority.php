<?php

namespace App\CitizenProject;

use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Entity\CitizenProjectMembership;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\Repository\CitizenProjectMembershipRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectAuthority
{
    private $membershipRepository;
    private $dispatcher;

    public function __construct(
        CitizenProjectMembershipRepository $membershipRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->membershipRepository = $membershipRepository;
        $this->dispatcher = $dispatcher;
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

        if (CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR === $privilege) {
            $membershipAdmins = $this->membershipRepository->findPrivilegedMemberships(
                $citizenProject,
                [CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR]
            );
            foreach ($membershipAdmins as $membershipAdmin) {
                $membershipAdmin->setPrivilege(CitizenProjectMembership::CITIZEN_PROJECT_FOLLOWER);
            }
        }

        $membership->setPrivilege($privilege);

        $this->dispatcher->dispatch(UserEvents::USER_UPDATE_CITIZEN_PROJECT_PRIVILEGE, new UserEvent($adherent));
    }

    private function getCitizenProjectMembership(
        Adherent $adherent,
        CitizenProject $citizenProject
    ): ?CitizenProjectMembership {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related citizen project memberships entities.
        if ($adherent->hasLoadedCitizenProjectMemberships()) {
            return $adherent->getCitizenProjectMembershipFor($citizenProject);
        }

        return $this->membershipRepository->findCitizenProjectMembership($adherent, $citizenProject);
    }
}
