<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
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
 *
 * @Algolia\Index(autoIndex=false)
 */
class CommitteeMembership
{
    const COMMITTEE_HOST = 'HOST';
    const COMMITTEE_FOLLOWER = 'FOLLOWER';
    const COMMITTEE_SUPERVISOR = 'SUPERVISOR';

    const PRIVILEGES = [
        self::COMMITTEE_HOST,
        self::COMMITTEE_FOLLOWER,
        self::COMMITTEE_SUPERVISOR,
    ];

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
     * @param string        $subscriptionDate
     */
    private function __construct(
        UuidInterface $uuid,
        UuidInterface $committeeUuid,
        Adherent $adherent,
        string $privilege = self::COMMITTEE_FOLLOWER,
        string $subscriptionDate = 'now'
    ) {
        $this->uuid = $uuid;
        $this->committeeUuid = $committeeUuid;
        $this->adherent = $adherent;
        $this->privilege = $privilege;
        $this->joinedAt = new \DateTime($subscriptionDate);
    }

    public static function getHostPrivileges(): array
    {
        return [self::COMMITTEE_SUPERVISOR, self::COMMITTEE_HOST];
    }

    public static function createForSupervisor(UuidInterface $committeeUuid, Adherent $supervisor, string $subscriptionDate = 'now'): self
    {
        $committeeUuid = clone $committeeUuid;

        return new self(
            self::createUuid($supervisor->getUuid(), $committeeUuid),
            $committeeUuid,
            $supervisor,
            self::COMMITTEE_SUPERVISOR,
            $subscriptionDate
        );
    }

    public static function createForHost(UuidInterface $committeeUuid, Adherent $host, string $subscriptionDate = 'now'): self
    {
        $committeeUuid = clone $committeeUuid;

        return new self(
            self::createUuid($host->getUuid(), $committeeUuid),
            $committeeUuid,
            $host,
            self::COMMITTEE_HOST,
            $subscriptionDate
        );
    }

    /**
     * Creates a new membership relationship between an adherent and a committee.
     */
    public static function createForAdherent(
        UuidInterface $committeeUuid,
        Adherent $adherent,
        string $privilege = self::COMMITTEE_FOLLOWER,
        string $subscriptionDate = 'now'
    ): self {
        $committeeUuid = clone $committeeUuid;

        return new self(
            self::createUuid($adherent->getUuid(), $committeeUuid),
            $committeeUuid,
            $adherent,
            $privilege,
            $subscriptionDate
        );
    }

    public static function checkPrivilege(string $privilege): void
    {
        if (!in_array($privilege, self::PRIVILEGES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid privilege %s', $privilege));
        }
    }

    /**
     * Returns whether or not this membership is a supervisor privileged membership.
     */
    public function isSupervisor(): bool
    {
        return self::COMMITTEE_SUPERVISOR === $this->privilege;
    }

    /**
     * Returns whether or not this membership is a host privileged membership.
     */
    public function isHostMember(): bool
    {
        return self::COMMITTEE_HOST === $this->privilege;
    }

    /**
     * Returns whether or not this membership is a follower privileged membership.
     */
    public function isFollower(): bool
    {
        return self::COMMITTEE_FOLLOWER === $this->privilege;
    }

    /**
     * Returns whether or not this membership enables the adherent to host a
     * committee.
     */
    public function canHostCommittee(): bool
    {
        return in_array($this->privilege, self::getHostPrivileges(), true);
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherent->getUuid();
    }

    public function getCommitteeUuid(): UuidInterface
    {
        return $this->committeeUuid;
    }

    public function setPrivilege(string $privilege): void
    {
        $this->privilege = $privilege;
    }

    public function isPromotableHost(): bool
    {
        return $this->isFollower();
    }

    public function promote(): void
    {
        if (!$this->isPromotableHost()) {
            throw CommitteeMembershipException::createNotPromotableHostPrivilegeException($this->uuid);
        }

        $this->privilege = self::COMMITTEE_HOST;
    }

    public function isDemotableHost(): bool
    {
        return $this->isHostMember();
    }

    public function demote(): void
    {
        if (!$this->isDemotableHost()) {
            throw CommitteeMembershipException::createNotDemotableFollowerPrivilegeException($this->uuid);
        }

        $this->privilege = self::COMMITTEE_FOLLOWER;
    }

    public function getSubscriptionDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->joinedAt->format(DATE_RFC822), $this->joinedAt->getTimezone());
    }

    public function matches(Adherent $adherent, Committee $committee): bool
    {
        return $adherent->equals($this->getAdherent()) && $this->committeeUuid->equals($committee->getUuid());
    }

    private static function createUuid(UuidInterface $adherentUuid, UuidInterface $committeeUuid): UuidInterface
    {
        $key = sha1(sprintf('%s|%s', $adherentUuid, $committeeUuid));

        return Uuid::uuid5(Uuid::NAMESPACE_OID, $key);
    }
}
