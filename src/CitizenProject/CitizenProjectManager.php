<?php

namespace AppBundle\CitizenProject;

use AppBundle\Collection\CitizenProjectMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CitizenProjectMembershipRepository;
use AppBundle\Repository\CitizenProjectRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

class CitizenProjectManager
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function isPromotableAdministrator(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        if (!$membership = $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject->getUuid())) {
            return false;
        }

        return $membership->isFollower();
    }

    public function isDemotableAdministrator(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        if (!$membership = $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject->getUuid())) {
            return false;
        }

        return $membership->isAdministrator();
    }

    public function isCitizenProjectAdministrator(Adherent $adherent): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related citizen project memberships entities.
        if ($adherent->isAdministrator()) {
            return true;
        }

        return $this->getCitizenProjectMembershipRepository()->administrateCitizenProject($adherent);
    }

    public function administrateCitizenProject(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related citizen project memberships entities.
        if ($adherent->isAdministratorOf($citizenProject)) {
            return true;
        }

        return $this->getCitizenProjectMembershipRepository()->administrateCitizenProject($adherent, $citizenProject->getUuid());
    }

    public function getAdherentCitizenProjects(Adherent $adherent): array
    {
        return $this->doGetAdherentCitizenProjects($adherent);
    }

    public function getAdherentCitizenProjectsAdministrator(Adherent $adherent): array
    {
        return $this->doGetAdherentCitizenProjects($adherent, true);
    }

    private function doGetAdherentCitizenProjects(Adherent $adherent, $onlyAdministrator = false): array
    {
        // Prevent SQL query if the adherent doesn't follow any citizen projects yet.
        if (!count($memberships = $adherent->getCitizenProjectMemberships())) {
            return [];
        }

        if (true === $onlyAdministrator) {
            $memberships = $memberships->getCitizenProjectAdministratorMemberships();
        }

        $citizenProjects = $this
            ->getCitizenProjectRepository()
            ->findCitizenProjects($memberships->getCitizenProjectUuids(), CitizenProjectRepository::INCLUDE_UNAPPROVED)
            ->filter(function (CitizenProject $citizenProject) use ($adherent) {
                // Any approved citizen project is kept.
                if ($citizenProject->isApproved()) {
                    return $citizenProject;
                }

                // However, an unapproved citizen project is kept only if it was created by the adherent.
                if ($citizenProject->isCreatedBy($adherent->getUuid())) {
                    return $citizenProject;
                }
            });

        return $citizenProjects->toArray();
    }

    public function countCitizenProjectAdministrators(CitizenProject $citizenProject): int
    {
        return $this->getCitizenProjectMembershipRepository()->countAdministratorMembers($citizenProject->getUuid());
    }

    public function getCitizenProjectAdministrators(CitizenProject $citizenProject): AdherentCollection
    {
        return $this->getCitizenProjectMembershipRepository()->findAdministrators($citizenProject->getUuid());
    }

    public function getCitizenProjectCreator(CitizenProject $citizenProject): Adherent
    {
        return $this->getAdherentRepository()->findOneByUuid($citizenProject->getCreatedBy());
    }

    public function getCitizenProjectMembers(CitizenProject $citizenProject): AdherentCollection
    {
        return $this->getCitizenProjectMembershipRepository()->findMembers($citizenProject->getUuid());
    }

    public function getCitizenProjectFollowers(CitizenProject $citizenProject): AdherentCollection
    {
        return $this->getCitizenProjectMembershipRepository()->findPriviledgedMembers($citizenProject->getUuid(), [CitizenProjectMembership::CITIZEN_PROJECT_FOLLOWER]);
    }

    public function getCitizenProjectMemberships(CitizenProject $citizenProject): CitizenProjectMembershipCollection
    {
        return $this->getCitizenProjectMembershipRepository()->findCitizenProjectMemberships($citizenProject->getUuid());
    }

    public function getCitizenProjectMembership(Adherent $adherent, CitizenProject $citizenProject): ?CitizenProjectMembership
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related citizen project memberships entities.
        if ($membership = $adherent->getCitizenProjectMembershipFor($citizenProject)) {
            return $membership;
        }

        return $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject->getUuid());
    }

    /**
     * Promotes an adherent to be an administrator of a citizen project.
     *
     * @param Adherent       $adherent
     * @param CitizenProject $citizenProject
     * @param bool           $flush
     */
    public function promote(Adherent $adherent, CitizenProject $citizenProject, bool $flush = true): void
    {
        $membership = $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject->getUuid());
        $membership->promote();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Make an adherent to be a member of a citizen project.
     *
     * @param Adherent       $adherent
     * @param CitizenProject $citizenProject
     * @param bool           $flush
     */
    public function demote(Adherent $adherent, CitizenProject $citizenProject, bool $flush = true): void
    {
        $membership = $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject->getUuid());
        $membership->demote();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Approves one citizen project.
     *
     * @param CitizenProject $citizenProject
     * @param bool           $flush
     */
    public function approveCitizenProject(CitizenProject $citizenProject, bool $flush = true): void
    {
        $citizenProject->approved();

        $creator = $this->getAdherentRepository()->findOneByUuid($citizenProject->getCreatedBy());
        $this->changePrivilege($creator, $citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR);

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Refuses one citizen project.
     *
     * @param CitizenProject $citizenProject
     * @param bool           $flush
     */
    public function refuseCitizenProject(CitizenProject $citizenProject, bool $flush = true): void
    {
        $citizenProject->refused();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function isFollowingCitizenProject(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        return $this->getCitizenProjectMembership($adherent, $citizenProject) instanceof CitizenProjectMembership;
    }

    /**
     * Makes an adherent follow multiple citizen projects at once.
     *
     * @param Adherent $adherent        The follower
     * @param string[] $citizenProjects An array of citizen project UUIDs
     */
    public function followCitizenProjects(Adherent $adherent, array $citizenProjects): void
    {
        if (empty($citizenProjects)) {
            return;
        }

        foreach ($this->getCitizenProjectRepository()->findByUuid($citizenProjects) as $citizenProject) {
            if (!$this->isFollowingCitizenProject($adherent, $citizenProject)) {
                $this->followCitizenProject($adherent, $citizenProject, false);
            }
        }

        $this->getManager()->flush();
    }

    /**
     * Makes an adherent follow one citizen project.
     *
     * @param Adherent       $adherent       The follower
     * @param CitizenProject $citizenProject The citizen project to follow
     * @param bool           $flush          Whether or not to flush the transaction
     */
    public function followCitizenProject(Adherent $adherent, CitizenProject $citizenProject, $flush = true): void
    {
        $manager = $this->getManager();
        $manager->persist($adherent->followCitizenProject($citizenProject));

        if ($flush) {
            $manager->flush();
        }
    }

    /**
     * Makes an adherent unfollow one citizen project.
     *
     * @param Adherent       $adherent       The follower
     * @param CitizenProject $citizenProject The citizen project to follow
     * @param bool           $flush          Whether or not to flush the transaction
     */
    public function unfollowCitizenProject(Adherent $adherent, CitizenProject $citizenProject, bool $flush = true): void
    {
        $membership = $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject->getUuid());

        if ($membership) {
            $this->doUnfollowCitizenProject($membership, $citizenProject, $flush);
        }
    }

    private function doUnfollowCitizenProject(CitizenProjectMembership $membership, CitizenProject $citizenProject, bool $flush = true): void
    {
        $manager = $this->getManager();

        $manager->remove($membership);
        $citizenProject->decrementMembersCount();

        if ($flush) {
            $manager->flush();
        }
    }

    private function getManager(): ObjectManager
    {
        return $this->registry->getManager();
    }

    private function getCitizenProjectRepository(): CitizenProjectRepository
    {
        return $this->registry->getRepository(CitizenProject::class);
    }

    private function getCitizenProjectMembershipRepository(): CitizenProjectMembershipRepository
    {
        return $this->registry->getRepository(CitizenProjectMembership::class);
    }

    private function getAdherentRepository(): AdherentRepository
    {
        return $this->registry->getRepository(Adherent::class);
    }

    public function countApprovedCitizenProjects(): int
    {
        return $this->getCitizenProjectRepository()->countApprovedCitizenProjects();
    }

    public function changePrivilege(Adherent $adherent, CitizenProject $citizenProject, string $privilege): void
    {
        CitizenProjectMembership::checkPrivilege($privilege);

        if (!$citizenProjectMembership = $this->getCitizenProjectMembership($adherent, $citizenProject)) {
            return;
        }

        $citizenProjectMembership->setPrivilege($privilege);

        $this->getManager()->flush();
    }
}
