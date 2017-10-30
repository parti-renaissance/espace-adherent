<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Exception\GroupMembershipException;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity represents a group membership.
 *
 * Each instance of GroupMembership means an Adherent is a member of a Group.
 *
 * @ORM\Table(
 *   name="group_memberships",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *       name="adherent_has_joined_group",
 *       columns={"adherent_id", "group_uuid"}
 *     )
 *   },
 *   indexes={
 *     @ORM\Index(name="groups_memberships_role_idx", columns="privilege")
 *   }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupMembershipRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class GroupMembership
{
    const GROUP_ADMINISTRATOR = 'ADMINISTRATOR';
    const GROUP_FOLLOWER = 'FOLLOWER';

    const PRIVILEGES = [
        self::GROUP_ADMINISTRATOR,
        self::GROUP_FOLLOWER,
    ];

    use EntityIdentityTrait;

    /**
     * The group UUID.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $groupUuid;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="Adherent", inversedBy="memberships")
     */
    private $adherent;

    /**
     * The privilege given to the member in the group.
     *
     * Privilege is either ADMINISTRATOR or FOLLOWER.
     *
     * @var string
     *
     * @ORM\Column(length=15)
     */
    private $privilege;

    /**
     * The date and time when the adherent joined the group.
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
     * @param UuidInterface $groupUuid
     * @param Adherent      $adherent
     * @param string        $privilege
     * @param string        $subscriptionDate
     */
    private function __construct(
        UuidInterface $uuid,
        UuidInterface $groupUuid,
        Adherent $adherent,
        string $privilege = self::GROUP_FOLLOWER,
        string $subscriptionDate = 'now'
    ) {
        $this->uuid = $uuid;
        $this->groupUuid = $groupUuid;
        $this->adherent = $adherent;
        $this->privilege = $privilege;
        $this->joinedAt = new \DateTime($subscriptionDate);
    }

    public static function createForAdministrator(UuidInterface $groupUuid, Adherent $administrator, string $subscriptionDate = 'now'): self
    {
        $groupUuid = clone $groupUuid;

        return new self(
            self::createUuid($administrator->getUuid(), $groupUuid),
            $groupUuid,
            $administrator,
            self::GROUP_ADMINISTRATOR,
            $subscriptionDate
        );
    }

    /**
     * Creates a new membership relationship between an adherent and a group.
     */
    public static function createForAdherent(
        UuidInterface $groupUuid,
        Adherent $adherent,
        string $privilege = self::GROUP_FOLLOWER,
        string $subscriptionDate = 'now'
    ): self {
        $groupUuid = clone $groupUuid;

        return new self(
            self::createUuid($adherent->getUuid(), $groupUuid),
            $groupUuid,
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

    private static function createUuid(UuidInterface $adherentUuid, UuidInterface $groupUuid): UuidInterface
    {
        $key = sha1(sprintf('%s|%s', $adherentUuid, $groupUuid));

        return Uuid::uuid5(Uuid::NAMESPACE_OID, $key);
    }

    /**
     * Returns whether or not this membership is a administrator privileged membership.
     */
    public function isAdministrator(): bool
    {
        return self::GROUP_ADMINISTRATOR === $this->privilege;
    }

    /**
     * Returns whether or not this membership is a follower privileged membership.
     */
    public function isFollower(): bool
    {
        return self::GROUP_FOLLOWER === $this->privilege;
    }

    /**
     * Returns whether or not this membership enables the adherent to administrate a group.
     */
    public function canAdministrateGroup(): bool
    {
        return self::GROUP_ADMINISTRATOR === $this->privilege;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherent->getUuid();
    }

    public function getGroupUuid(): UuidInterface
    {
        return $this->groupUuid;
    }

    public function setPrivilege(string $privilege): void
    {
        $this->privilege = $privilege;
    }

    public function isPromotableAdministrator(): bool
    {
        return $this->isFollower();
    }

    public function promote(): void
    {
        if (!$this->isPromotableAdministrator()) {
            throw GroupMembershipException::createNotPromotableAdministratorPrivilegeException($this->uuid);
        }

        $this->privilege = self::GROUP_ADMINISTRATOR;
    }

    public function isDemotableAdministrator(): bool
    {
        return $this->isAdministrator();
    }

    public function demote(): void
    {
        if (!$this->isDemotableAdministrator()) {
            throw GroupMembershipException::createNotDemotableFollowerPrivilegeException($this->uuid);
        }

        $this->privilege = self::GROUP_FOLLOWER;
    }

    public function getSubscriptionDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->joinedAt->format(DATE_RFC822), $this->joinedAt->getTimezone());
    }

    public function matches(Adherent $adherent, Group $group): bool
    {
        return $adherent->equals($this->getAdherent()) && $this->groupUuid->equals($group->getUuid());
    }
}
