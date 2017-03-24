<?php

namespace AppBundle\Entity;

use AppBundle\Exception\CommitteeMembershipException;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity represents a committee membership.
 *
 * Each instance of CommitteeMembership means an Adherent is a member of a Committee.
 *
 * @ORM\Table(
 *   name="committees_memberships",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *       name="adherent_has_joined_committee",
 *       columns={"adherent_id", "committee_uuid"}
 *     )
 *   },
 *   indexes={
 *     @ORM\Index(name="committees_memberships_role_idx", columns="privilege")
 *   }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeMembershipRepository")
 */
class CommitteeMembership
{
    const COMMITTEE_HOST = 'HOST';
    const COMMITTEE_FOLLOWER = 'FOLLOWER';
    const COMMITTEE_SUPERVISOR = 'SUPERVISOR';

    use EntityIdentityTrait;

    /**
     * The committee UUID.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $committeeUuid;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="Adherent", inversedBy="memberships")
     */
    private $adherent;

    /**
     * The privilege given to the member in the committee.
     *
     * Privilege is either HOST or FOLLOWER.
     *
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $privilege;

    /**
     * The date and time when the adherent joined the committee.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $joinedAt;

    /**
     * Constructor.
     *
     * @param UuidInterface $uuid
     * @param UuidInterface $committeeUuid
     * @param Adherent      $adherent
     * @param string        $privilege
     */
    private function __construct(
        UuidInterface $uuid,
        UuidInterface $committeeUuid,
        Adherent $adherent,
        string $privilege = self::COMMITTEE_FOLLOWER
    ) {
        $this->uuid = $uuid;
        $this->committeeUuid = $committeeUuid;
        $this->adherent = $adherent;
        $this->privilege = $privilege;
        $this->joinedAt = new \DateTime();
    }

    /**
     * Returns the list of committee host privileges.
     *
     * @return array
     */
    final public static function getHostPrivileges(): array
    {
        return [self::COMMITTEE_SUPERVISOR, self::COMMITTEE_HOST];
    }

    public static function createForSupervisor(UuidInterface $committeeUuid, Adherent $supervisor): self
    {
        $committeeUuid = clone $committeeUuid;

        return new self(
            self::createUuid($supervisor->getUuid(), $committeeUuid),
            $committeeUuid,
            $supervisor,
            self::COMMITTEE_SUPERVISOR
        );
    }

    public static function createForHost(UuidInterface $committeeUuid, Adherent $host): self
    {
        $committeeUuid = clone $committeeUuid;

        return new self(
            self::createUuid($host->getUuid(), $committeeUuid),
            $committeeUuid,
            $host,
            self::COMMITTEE_HOST
        );
    }

    /**
     * Creates a new membership relationship between an adherent and a committee.
     *
     * @param UuidInterface $committeeUuid
     * @param Adherent      $adherent
     * @param string        $privilege
     *
     * @return CommitteeMembership
     */
    public static function createForAdherent(
        UuidInterface $committeeUuid,
        Adherent $adherent,
        string $privilege = self::COMMITTEE_FOLLOWER
    ): self {
        $committeeUuid = clone $committeeUuid;

        return new self(
            self::createUuid($adherent->getUuid(), $committeeUuid),
            $committeeUuid,
            $adherent,
            $privilege
        );
    }

    /**
     * Computes a unique UUID with the provided adherent and committee UUIDs.
     *
     * @param UuidInterface $adherentUuid
     * @param UuidInterface $committeeUuid
     *
     * @return UuidInterface
     */
    private static function createUuid(UuidInterface $adherentUuid, UuidInterface $committeeUuid): UuidInterface
    {
        $key = sha1(sprintf('%s|%s', $adherentUuid, $committeeUuid));

        return Uuid::uuid5(Uuid::NAMESPACE_OID, $key);
    }

    /**
     * Returns whether or not this membership is a supervisor priviledged membership.
     *
     * @return bool
     */
    public function isSupervisor(): bool
    {
        return self::COMMITTEE_SUPERVISOR === $this->privilege;
    }

    /**
     * Returns whether or not this membership is a host priviledged membership.
     *
     * @return bool
     */
    public function isHostMember(): bool
    {
        return self::COMMITTEE_HOST === $this->privilege;
    }

    /**
     * Returns whether or not this membership is a follower priviledged membership.
     *
     * @return bool
     */
    public function isFollower(): bool
    {
        return self::COMMITTEE_FOLLOWER === $this->privilege;
    }

    /**
     * Returns whether or not this memberships enables the adherent
     * to host a committee.
     *
     * @return bool
     */
    public function canHostCommittee(): bool
    {
        return in_array($this->privilege, self::getHostPrivileges(), true);
    }

    /**
     * Returns the adherent.
     *
     * @return Adherent|null
     */
    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    /**
     * Returns the adherent UUID.
     *
     * @return UuidInterface
     */
    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherent->getUuid();
    }

    /**
     * Returns the adherent UUID.
     *
     * @return UuidInterface
     */
    public function getCommitteeUuid(): UuidInterface
    {
        return $this->committeeUuid;
    }

    public function setPrivilege(string $privilege): void
    {
        $this->privilege = $privilege;
    }

    public function promote(): void
    {
        if (!$this->isFollower()) {
            throw CommitteeMembershipException::createNotPromotableHostPrivilegeException($this->uuid);
        }

        $this->privilege = self::COMMITTEE_HOST;
    }

    public function demote(): void
    {
        if (!$this->isHostMember()) {
            throw CommitteeMembershipException::createNotDemotableFollowerPrivilegeException($this->uuid);
        }

        $this->privilege = self::COMMITTEE_FOLLOWER;
    }
}
