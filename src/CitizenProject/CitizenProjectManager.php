<?php

namespace AppBundle\CitizenProject;

use AppBundle\Collection\CitizenProjectMembershipCollection;
use AppBundle\Coordinator\Filter\CitizenProjectFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCommitteeSupport;
use AppBundle\Entity\CitizenProjectComment;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\CitizenProjectCommentRepository;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Entity\Committee;
use AppBundle\Exception\CitizenProjectCommitteeSupportAlreadySupportException;
use AppBundle\Exception\CitizenProjectNotApprovedException;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CitizenProjectCommitteeSupportRepository;
use AppBundle\Repository\CitizenProjectMembershipRepository;
use AppBundle\Repository\CitizenProjectRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CitizenProjectManager
{
    public const STATUS_NOT_ALLOWED_TO_CREATE = [
        CitizenProject::PENDING,
        CitizenProject::REFUSED,
    ];

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

    public function getCoordinatorCitizenProjects(Adherent $coordinator, CitizenProjectFilter $filter): array
    {
        $projects = $this->getCitizenProjectRepository()->findManagedByCoordinator($coordinator, $filter);

        array_walk($projects, function (CitizenProject $project) {
            if ($project->getCreatedBy()) {
                $project->setCreator($this->getAdherentRepository()->findByUuid($project->getCreatedBy()));
            }
        });

        return $projects;
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

    public function getCitizenProjectCreator(CitizenProject $citizenProject): ?Adherent
    {
        return $this->getAdherentRepository()->findOneByUuid($citizenProject->getCreatedBy());
    }

    public function getCitizenProjectNextAction(CitizenProject $citizenProject): ?CitizenAction
    {
        return $this->getCitizenActionRepository()->findNextCitizenActionForCitizenProject($citizenProject);
    }

    /**
     * @param CitizenProject[] $citizenProjects
     */
    public function injectCitizenProjectCreator(array $citizenProjects): void
    {
        foreach ($citizenProjects as $citizenProject) {
            $citizenProject->setCreator($this->getCitizenProjectCreator($citizenProject));
        }
    }

    /**
     * @param CitizenProject[] $citizenProjects
     */
    public function injectCitizenProjectAdministrators(array $citizenProjects): void
    {
        foreach ($citizenProjects as $citizenProject) {
            $citizenProject->setAdministrators($this->getCitizenProjectAdministrators($citizenProject));
        }
    }

    /**
     * @param CitizenProject[] $citizenProjects
     */
    public function injectCitizenProjectNextAction(array $citizenProjects): void
    {
        foreach ($citizenProjects as $citizenProject) {
            $citizenProject->setNextAction($this->getCitizenProjectNextAction($citizenProject));
        }
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

    public function getCitizenProjectComments(CitizenProject $citizenProject): array
    {
        return $this->getCitizenProjectCommentRepository()->findForProject($citizenProject);
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

    /**
     * @param CitizenProject $citizenProject
     * @param bool           $flush
     */
    public function preRefuseCitizenProject(CitizenProject $citizenProject, bool $flush = true): void
    {
        $citizenProject->preRefused();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * @param CitizenProject $project
     * @param bool           $flush
     */
    public function preApproveCitizenProject(CitizenProject $project, bool $flush = true): void
    {
        $project->preApproved();

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

    private function getCitizenProjectCommitteeSupportRepository(): CitizenProjectCommitteeSupportRepository
    {
        return $this->registry->getRepository(CitizenProjectCommitteeSupport::class);
    }

    private function getCitizenProjectCommentRepository(): CitizenProjectCommentRepository
    {
        return $this->registry->getRepository(CitizenProjectComment::class);
    }

    private function getCitizenActionRepository(): CitizenActionRepository
    {
        return $this->registry->getRepository(CitizenAction::class);
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

    public function findAdherentNearCitizenProjectOrAcceptAllNotification(CitizenProject $citizenProject, int $offset = 0, bool $excludeSupervisor = true, int $radius = CitizenProjectMessageNotifier::RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN): Paginator
    {
        return $this->getAdherentRepository()->findByNearCitizenProjectOrAcceptAllNotification($citizenProject, $offset, $excludeSupervisor, $radius);
    }

    public function approveCommitteeSupport(Committee $committee, CitizenProject $citizenProject, bool $flush = true): void
    {
        if (!$citizenProject->isApproved()) {
            throw new CitizenProjectNotApprovedException($citizenProject);
        }

        if (!$committeeSupport = $this->getCitizenProjectCommitteeSupportRepository()->findByCommittee($committee)) {
            $committeeSupport = new CitizenProjectCommitteeSupport($citizenProject, $committee);
        }

        if ($committeeSupport->isApproved()) {
            throw new CitizenProjectCommitteeSupportAlreadySupportException(
                $committeeSupport->getCommittee(),
                $committeeSupport->getCitizenProject()
            );
        }

        $committeeSupport->approve();

        if ($flush) {
            $this->getManager()->persist($committeeSupport);
            $this->getManager()->flush();
        }
    }

    public function removeAuthorItems(Adherent $adherent)
    {
        $this->getCitizenProjectCommentRepository()->removeForAuthor($adherent);
    }

    public function hasCitizenProjectInStatus(Adherent $adherent, array $status): bool
    {
        return $this->getCitizenProjectRepository()->hasCitizenProjectInStatus($adherent, $status);
    }
}
