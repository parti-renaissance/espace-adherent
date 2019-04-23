<?php

namespace AppBundle\CitizenProject;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Collection\CitizenProjectMembershipCollection;
use AppBundle\Coordinator\Filter\CitizenProjectFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCommitteeSupport;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Entity\Committee;
use AppBundle\Entity\TurnkeyProject;
use AppBundle\Events;
use AppBundle\Exception\CitizenProjectCommitteeSupportAlreadySupportException;
use AppBundle\Exception\CitizenProjectNotApprovedException;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\CitizenProjectCommitteeSupportRepository;
use AppBundle\Repository\CitizenProjectMembershipRepository;
use AppBundle\Repository\CitizenProjectRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CitizenProjectManager
{
    public const CITIZEN_PROJECT_DEFAULT_IMAGE_NAME = 'default.png';

    private $registry;
    private $storage;
    private $projectAuthority;
    private $eventDispatcher;

    /**
     * @var Server
     */
    private $glide;

    public function __construct(
        RegistryInterface $registry,
        Filesystem $storage,
        CitizenProjectAuthority $projectAuthority,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->registry = $registry;
        $this->storage = $storage;
        $this->projectAuthority = $projectAuthority;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setGlide(Server $glide): void
    {
        $this->glide = $glide;
    }

    /**
     * @return CitizenProject[]
     */
    public function getCoordinatorCitizenProjects(Adherent $coordinator, CitizenProjectFilter $filter): array
    {
        $projects = $this->getCitizenProjectRepository()->findManagedByCoordinator($coordinator, $filter);

        $this->injectCitizenProjectCreator($projects);

        return $projects;
    }

    /**
     * @return CitizenProject[]
     */
    public function getNearCitizenProjectByCoordinates(Coordinates $coordinates, int $limit = 3): array
    {
        return $this->getCitizenProjectRepository()->findNearCitizenProjectByCoordinates($coordinates, $limit);
    }

    public function getCitizenProjectAdministrators(CitizenProject $citizenProject): AdherentCollection
    {
        return $this->getCitizenProjectMembershipRepository()->findAdministrators($citizenProject);
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
     * @return CitizenAction[]
     */
    public function getCitizenProjectNextActions(CitizenProject $citizenProject, int $maxResults = 5): array
    {
        return $this->getCitizenActionRepository()->findNextCitizenActionsForCitizenProject($citizenProject, $maxResults);
    }

    /**
     * @return CitizenAction[]
     */
    public function getCitizenProjectActions(CitizenProject $citizenProject): array
    {
        return $this->getCitizenActionRepository()->findCitizenActionsByCitizenProject($citizenProject);
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
            if ($action = $this->getCitizenProjectNextAction($citizenProject)) {
                $citizenProject->setNextAction($action);
            }
        }
    }

    public function getCitizenProjectMembers(CitizenProject $citizenProject): AdherentCollection
    {
        return $this->getCitizenProjectMembershipRepository()->findMembers($citizenProject);
    }

    public function getCitizenProjectFollowers(
        CitizenProject $citizenProject,
        bool $withHosts = false
    ): AdherentCollection {
        return $this
            ->getCitizenProjectMembershipRepository()
            ->findFollowers($citizenProject, $withHosts)
        ;
    }

    public function getCitizenProjectMemberships(CitizenProject $citizenProject): CitizenProjectMembershipCollection
    {
        return $this->getCitizenProjectMembershipRepository()->findCitizenProjectMemberships($citizenProject);
    }

    public function getOptinCitizenProjectFollowers(CitizenProject $citizenProject): AdherentCollection
    {
        $followers = $this->getCitizenProjectFollowers($citizenProject);

        return $this
            ->getCitizenProjectAdministrators($citizenProject)
            ->merge($followers->getCommitteesNotificationsSubscribers())
        ;
    }

    /**
     * Promotes a member to be an administrator of a citizen project.
     */
    public function promote(Adherent $adherent, CitizenProject $citizenProject, bool $flush = true): void
    {
        $membership = $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject);
        $membership->promote();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Makes an administrator to be a simple member of a citizen project.
     */
    public function demote(Adherent $adherent, CitizenProject $citizenProject, bool $flush = true): void
    {
        $membership = $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject);
        $membership->demote();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function approveCitizenProject(CitizenProject $citizenProject, bool $flush = true): void
    {
        $citizenProject->approved();

        /** @var Adherent $creator */
        $creator = $this->getAdherentRepository()->findOneByUuid($citizenProject->getCreatedBy());
        $this->projectAuthority->changePrivilege($creator, $citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR);

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function refuseCitizenProject(CitizenProject $citizenProject, bool $flush = true): void
    {
        $citizenProject->refused();

        foreach ($this->getCitizenProjectAdministrators($citizenProject) as $administrator) {
            $this->projectAuthority->changePrivilege($administrator, $citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_FOLLOWER);
        }

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function preRefuseCitizenProject(CitizenProject $citizenProject, bool $flush = true): void
    {
        $citizenProject->preRefused();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function preApproveCitizenProject(CitizenProject $project, bool $flush = true): void
    {
        $project->preApproved();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function followCitizenProject(Adherent $adherent, CitizenProject $citizenProject, bool $flush = true): void
    {
        $manager = $this->getManager();
        $manager->persist($adherent->followCitizenProject($citizenProject));

        if ($flush) {
            $manager->flush();
        }

        $this->eventDispatcher->dispatch(Events::CITIZEN_PROJECT_UPDATED, new CitizenProjectWasUpdatedEvent($citizenProject));
        $this->eventDispatcher->dispatch(Events::CITIZEN_PROJECT_FOLLOWER_ADDED, new CitizenProjectFollowerChangeEvent($citizenProject, $adherent));
    }

    public function unfollowCitizenProject(Adherent $adherent, CitizenProject $citizenProject, bool $flush = true): void
    {
        if (!$membership = $this->getCitizenProjectMembershipRepository()->findCitizenProjectMembership($adherent, $citizenProject)) {
            return;
        }

        $manager = $this->getManager();

        $manager->remove($membership);
        $citizenProject->decrementMembersCount();

        if ($flush) {
            $manager->flush();
        }

        $this->eventDispatcher->dispatch(Events::CITIZEN_PROJECT_UPDATED, new CitizenProjectWasUpdatedEvent($citizenProject));
        $this->eventDispatcher->dispatch(Events::CITIZEN_PROJECT_FOLLOWER_REMOVED, new CitizenProjectFollowerChangeEvent($citizenProject, $adherent));
    }

    public function findAdherentNearCitizenProjectOrAcceptAllNotification(
        CitizenProject $citizenProject,
        int $offset = 0,
        bool $excludeSupervisor = true,
        int $radius = CitizenProjectMessageNotifier::RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN
    ): Paginator {
        return $this->getAdherentRepository()->findByNearCitizenProjectOrAcceptAllNotification($citizenProject, $offset, $excludeSupervisor, $radius);
    }

    public function approveCommitteeSupport(
        Committee $committee,
        CitizenProject $citizenProject,
        bool $flush = true
    ): void {
        if (!$citizenProject->isApproved()) {
            throw new CitizenProjectNotApprovedException($citizenProject);
        }

        if (!$committeeSupport = $this->findCommitteeSupport($committee, $citizenProject)) {
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
            $this->getManager()->flush();
        }
    }

    public function deleteCommitteeSupport(
        Committee $committee,
        CitizenProject $citizenProject,
        bool $flush = true
    ): void {
        if (!$committeeSupport = $this->findCommitteeSupport($committee, $citizenProject)) {
            throw new \RuntimeException('No CommitteeSupport found for committee '.$committee->getName());
        }

        $this->getManager()->remove($committeeSupport);

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Uploads and saves the citizen project image.
     */
    public function addImage(CitizenProject $citizenProject): void
    {
        if (!$citizenProject->getImage() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The image must be an instance of %s', UploadedFile::class));
        }

        // Clears the old image if needed
        if ($citizenProject->hasImageUploaded() && null !== $citizenProject->getImageName() && $oldImagePath = $citizenProject->getImagePath()) {
            $this->storage->delete($oldImagePath);
        }

        $citizenProject->setImageNameFromUploadedFile($citizenProject->getImage());
        $path = $citizenProject->getImagePath();

        // Uploads the file : creates or updates if exists
        $this->storage->put($path, file_get_contents($citizenProject->getImage()->getPathname()));

        // Clears the cache file
        $this->glide->deleteCache($path);

        $citizenProject->setImageUploaded(true);
    }

    /**
     * Copies turnkey project image to the citizen project.
     */
    public function copyImageFromTurnkeyProject(CitizenProject $citizenProject, TurnkeyProject $turnkeyProject): void
    {
        if ($turnkeyProject->getImageName()) {
            $citizenProject->setImageNameFromTurnkeyProject($turnkeyProject);
            $path = $citizenProject->getImagePath();

            $this->storage->put($path, $this->storage->read($turnkeyProject->getImagePath()));

            // Clears the cache file
            $this->glide->deleteCache($path);

            $citizenProject->setImageUploaded(true);
        }
    }

    /**
     * Adds default image to the citizen project.
     */
    public function setDefaultImage(CitizenProject $citizenProject): void
    {
        $citizenProject->setImageName(CitizenProjectManager::CITIZEN_PROJECT_DEFAULT_IMAGE_NAME);
        $citizenProject->setImageUploaded(false);
    }

    /**
     * Removes the citizen project image.
     */
    public function removeImage(CitizenProject $citizenProject): void
    {
        // Default image should not be removed
        if (self::CITIZEN_PROJECT_DEFAULT_IMAGE_NAME !== $citizenProject->getImageName()) {
            $path = $citizenProject->getImagePath();

            // Deletes the file
            $this->storage->delete($path);

            // Clears the cache file
            $this->glide->deleteCache($path);

            $citizenProject->setImageName(self::CITIZEN_PROJECT_DEFAULT_IMAGE_NAME);
            $citizenProject->setImageUploaded(false);
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

    private function getCitizenActionRepository(): CitizenActionRepository
    {
        return $this->registry->getRepository(CitizenAction::class);
    }

    private function findCommitteeSupport(
        Committee $committee,
        CitizenProject $citizenProject
    ): ?CitizenProjectCommitteeSupport {
        return $this
            ->getCitizenProjectCommitteeSupportRepository()
            ->findOneByCommitteeAndCitizenProject($committee, $citizenProject)
        ;
    }
}
