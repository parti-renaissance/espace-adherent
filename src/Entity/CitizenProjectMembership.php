<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Exception\CitizenProjectMembershipException;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity represents a citizen project membership.
 *
 * Each instance of CitizenProjectMembership means an Adherent is a member of a CitizenProject.
 *
 * @ORM\Table(
 *     name="citizen_project_memberships",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="adherent_has_joined_citizen_project",
 *             columns={"adherent_id", "citizen_project_id"}
 *         )
 *     },
 *     indexes={
 *         @ORM\Index(name="citizen_project_memberships_role_idx", columns="privilege")
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectMembershipRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProjectMembership
{
    public const CITIZEN_PROJECT_ADMINISTRATOR = 'HOST';
    public const CITIZEN_PROJECT_FOLLOWER = 'FOLLOWER';

    public const PRIVILEGES = [
        self::CITIZEN_PROJECT_ADMINISTRATOR,
        self::CITIZEN_PROJECT_FOLLOWER,
    ];

    use EntityIdentityTrait;

    /**
     * @var CitizenProject
     *
     * @ORM\ManyToOne(targetEntity="CitizenProject")
     * @ORM\JoinColumn(nullable=false)
     */
    private $citizenProject;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="Adherent", inversedBy="citizenProjectMemberships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adherent;

    /**
     * The privilege given to the member in the citizen project.
     *
     * Privilege is either ADMINISTRATOR or FOLLOWER.
     *
     * @var string
     *
     * @ORM\Column(length=15)
     */
    private $privilege;

    /**
     * The date and time when the adherent joined the citizen project.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $joinedAt;

    private function __construct(
        UuidInterface $uuid,
        CitizenProject $citizenProject,
        Adherent $adherent,
        string $privilege = self::CITIZEN_PROJECT_FOLLOWER,
        string $subscriptionDate = 'now'
    ) {
        $this->uuid = $uuid;
        $this->citizenProject = $citizenProject;
        $this->adherent = $adherent;
        $this->privilege = $privilege;
        $this->joinedAt = new \DateTime($subscriptionDate);
    }

    public static function createForAdministrator(
        CitizenProject $citizenProject,
        Adherent $administrator,
        string $subscriptionDate = 'now'
    ): self {
        return static::createForAdherent(
            $citizenProject,
            $administrator,
            self::CITIZEN_PROJECT_ADMINISTRATOR,
            $subscriptionDate
        );
    }

    /**
     * Creates a new membership relationship between an adherent and a citizen project.
     */
    public static function createForAdherent(
        CitizenProject $citizenProject,
        Adherent $adherent,
        string $privilege = self::CITIZEN_PROJECT_FOLLOWER,
        string $subscriptionDate = 'now'
    ): self {
        return new self(
            self::createUuid($adherent->getUuid(), clone $citizenProject->getUuid()),
            $citizenProject,
            $adherent,
            $privilege,
            $subscriptionDate
        );
    }

    public static function checkPrivilege(string $privilege): void
    {
        if (!\in_array($privilege, self::PRIVILEGES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid privilege %s', $privilege));
        }
    }

    /**
     * Returns whether or not this membership is a administrator privileged membership.
     */
    public function isAdministrator(): bool
    {
        return self::CITIZEN_PROJECT_ADMINISTRATOR === $this->privilege;
    }

    /**
     * Returns whether or not this membership is a follower privileged membership.
     */
    public function isFollower(): bool
    {
        return self::CITIZEN_PROJECT_FOLLOWER === $this->privilege;
    }

    /**
     * Returns whether or not this membership enables the adherent to administrate a citizen project.
     */
    public function canAdministrateCitizenProject(): bool
    {
        return self::CITIZEN_PROJECT_ADMINISTRATOR === $this->privilege;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherent->getUuid();
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }

    public function getCitizenProjectUuid(): UuidInterface
    {
        return $this->citizenProject->getUuid();
    }

    public function setPrivilege(string $privilege): void
    {
        static::checkPrivilege($privilege);

        $this->privilege = $privilege;
    }

    public function getPrivilege(): string
    {
        return $this->privilege;
    }

    public function isPromotableAdministrator(): bool
    {
        return $this->isFollower();
    }

    public function promote(): void
    {
        if (!$this->isPromotableAdministrator()) {
            throw CitizenProjectMembershipException::createNotPromotableAdministratorPrivilegeException($this->uuid);
        }

        $this->privilege = self::CITIZEN_PROJECT_ADMINISTRATOR;
    }

    public function isDemotableAdministrator(): bool
    {
        return $this->isAdministrator();
    }

    public function demote(): void
    {
        if (!$this->isDemotableAdministrator()) {
            throw CitizenProjectMembershipException::createNotDemotableFollowerPrivilegeException($this->uuid);
        }

        $this->privilege = self::CITIZEN_PROJECT_FOLLOWER;
    }

    public function getSubscriptionDate(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->joinedAt);
    }

    public function matches(Adherent $adherent, CitizenProject $citizenProject): bool
    {
        return $adherent->equals($this->getAdherent())
            && $this->citizenProject->getUuid()->equals($citizenProject->getUuid());
    }

    private static function createUuid(UuidInterface $adherentUuid, UuidInterface $citizenProjectUuid): UuidInterface
    {
        $key = sha1(sprintf('%s|%s', $adherentUuid, $citizenProjectUuid));

        return Uuid::uuid5(Uuid::NAMESPACE_OID, $key);
    }
}
