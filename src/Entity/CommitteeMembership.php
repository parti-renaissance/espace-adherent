<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * This entity represents a committee membership.
 *
 * Each instance of CommitteeMembership means an Adherent is a member of a Committee.
 *
 * @ORM\Table(
 *     name="committees_memberships",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="adherent_has_joined_committee",
 *             columns={"adherent_id", "committee_id"}
 *         ),
 *         @ORM\UniqueConstraint(
 *             name="adherent_votes_in_committee",
 *             columns={"adherent_id", "enable_vote"}
 *         )
 *     },
 *     indexes={
 *         @ORM\Index(name="committees_memberships_role_idx", columns="privilege")
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeMembershipRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CommitteeMembership
{
    public const COMMITTEE_HOST = 'HOST';
    public const COMMITTEE_FOLLOWER = 'FOLLOWER';
    public const COMMITTEE_SUPERVISOR = 'SUPERVISOR';

    public const PRIVILEGES = [
        self::COMMITTEE_HOST,
        self::COMMITTEE_FOLLOWER,
        self::COMMITTEE_SUPERVISOR,
    ];

    use EntityIdentityTrait;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="Adherent", inversedBy="memberships")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"export"})
     */
    private $adherent;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="Committee")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"adherent_committees_modal"})
     */
    private $committee;

    /**
     * The privilege given to the member in the committee.
     *
     * Privilege is either HOST, FOLLOWER or SUPERVISOR
     *
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Groups({"adherent_committees_modal"})
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
     * Indicates if the adherent votes in this committee
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enableVote;

    /**
     * @var CommitteeCandidacy|null
     *
     * @ORM\OneToOne(targetEntity="CommitteeCandidacy", cascade={"all"}, orphanRemoval=true)
     */
    private $committeeCandidacy;

    private function __construct(
        UuidInterface $uuid,
        Committee $committee,
        Adherent $adherent,
        string $privilege = self::COMMITTEE_FOLLOWER,
        string $subscriptionDate = 'now'
    ) {
        $this->uuid = $uuid;
        $this->committee = $committee;
        $this->adherent = $adherent;
        $this->privilege = $privilege;
        $this->joinedAt = new \DateTime($subscriptionDate);
    }

    final public static function getHostPrivileges(): array
    {
        return [self::COMMITTEE_SUPERVISOR, self::COMMITTEE_HOST];
    }

    public static function createForSupervisor(
        Committee $committee,
        Adherent $supervisor,
        string $subscriptionDate = 'now'
    ): self {
        return static::createForAdherent($committee, $supervisor, self::COMMITTEE_SUPERVISOR, $subscriptionDate);
    }

    public static function createForHost(Committee $committee, Adherent $host, string $subscriptionDate = 'now'): self
    {
        return static::createForAdherent($committee, $host, self::COMMITTEE_HOST, $subscriptionDate);
    }

    /**
     * Creates a new membership relationship between an adherent and a committee.
     */
    public static function createForAdherent(
        Committee $committee,
        Adherent $adherent,
        string $privilege = self::COMMITTEE_FOLLOWER,
        string $subscriptionDate = 'now'
    ): self {
        return new self(
            self::createUuid($adherent->getUuid(), $committee->getUuid()),
            $committee,
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

    public function getPrivilege(): string
    {
        return $this->privilege;
    }

    /**
     * Returns whether or not this membership enables the adherent to host a
     * committee.
     */
    public function canHostCommittee(): bool
    {
        return \in_array($this->privilege, self::getHostPrivileges(), true);
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherent->getUuid();
    }

    public function getCommitteeUuid(): UuidInterface
    {
        return $this->committee->getUuid();
    }

    public function setPrivilege(string $privilege): void
    {
        $this->privilege = $privilege;
    }

    public function getCommitteeCandidacy(): ?CommitteeCandidacy
    {
        return $this->committeeCandidacy;
    }

    public function setCommitteeCandidacy(CommitteeCandidacy $committeeCandidacy): void
    {
        $this->committeeCandidacy = $committeeCandidacy;
    }

    public function removeCandidacy(): void
    {
        $this->committeeCandidacy = null;
    }

    public function isVotingCommittee(): bool
    {
        return true === $this->enableVote;
    }

    public function enableVote(): void
    {
        $this->enableVote = true;
    }

    public function disableVote(): void
    {
        $this->enableVote = null;
    }

    public function isPromotableHost(): bool
    {
        return $this->isFollower();
    }

    public function isDemotableHost(): bool
    {
        return $this->isHostMember();
    }

    /**
     * @Groups({"export", "adherent_committees_modal"})
     */
    public function getSubscriptionDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->joinedAt->format(\DATE_RFC822), $this->joinedAt->getTimezone());
    }

    public function matches(Adherent $adherent, Committee $committee): bool
    {
        return $adherent->equals($this->getAdherent()) && $this->committee->getUuid()->equals($committee->getUuid());
    }

    private static function createUuid(UuidInterface $adherentUuid, UuidInterface $committeeUuid): UuidInterface
    {
        $key = sha1(sprintf('%s|%s', $adherentUuid->toString(), $committeeUuid->toString()));

        return Uuid::uuid5(Uuid::NAMESPACE_OID, $key);
    }
}
