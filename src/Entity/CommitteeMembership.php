<?php

namespace App\Entity;

use App\Committee\CommitteeMembershipTriggerEnum;
use App\Repository\CommitteeMembershipRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * This entity represents a committee membership.
 *
 * Each instance of CommitteeMembership means an Adherent is a member of a Committee.
 */
#[ORM\Entity(repositoryClass: CommitteeMembershipRepository::class)]
#[ORM\Index(columns: ['privilege'], name: 'committees_memberships_role_idx')]
#[ORM\Table(name: 'committees_memberships')]
#[ORM\UniqueConstraint(name: 'adherent_has_joined_committee', columns: ['adherent_id', 'committee_id'])]
#[ORM\UniqueConstraint(name: 'adherent_votes_in_committee', columns: ['adherent_id', 'enable_vote'])]
class CommitteeMembership implements UuidEntityInterface
{
    use EntityIdentityTrait;

    public const COMMITTEE_HOST = 'HOST';
    public const COMMITTEE_FOLLOWER = 'FOLLOWER';

    public const PRIVILEGES = [
        self::COMMITTEE_HOST,
        self::COMMITTEE_FOLLOWER,
    ];

    /**
     * @var Adherent
     */
    #[Groups(['export', 'api_candidacy_read', 'committee_candidacy:read', 'committee_election:read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'committeeMembership', targetEntity: Adherent::class)]
    private $adherent;

    /**
     * @var Committee
     */
    #[Groups(['adherent_committees_modal', 'profile_read', 'profile_update'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    private $committee;

    /**
     * The privilege given to the member in the committee.
     *
     * Privilege is either HOST or FOLLOWER
     *
     * @var string
     */
    #[Groups(['adherent_committees_modal'])]
    #[ORM\Column(length: 10)]
    private $privilege;

    /**
     * The date and time when the adherent joined the committee.
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $joinedAt;

    /**
     * Indicates if the adherent votes in this committee
     *
     * @var bool|null
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enableVote;

    /**
     * @var CommitteeCandidacy[]
     */
    #[ORM\OneToMany(mappedBy: 'committeeMembership', targetEntity: CommitteeCandidacy::class, cascade: ['all'], orphanRemoval: true)]
    private $committeeCandidacies;

    #[ORM\Column(name: '`trigger`', nullable: true, enumType: CommitteeMembershipTriggerEnum::class)]
    private ?CommitteeMembershipTriggerEnum $trigger;

    private function __construct(
        UuidInterface $uuid,
        Committee $committee,
        Adherent $adherent,
        string $privilege = self::COMMITTEE_FOLLOWER,
        ?\DateTimeInterface $subscriptionDate = null,
        ?CommitteeMembershipTriggerEnum $trigger = null,
    ) {
        $this->uuid = $uuid;
        $this->committee = $committee;
        $this->adherent = $adherent;
        $this->privilege = $privilege;
        $this->joinedAt = $subscriptionDate ?? new \DateTime();
        $this->trigger = $trigger;
        $this->committeeCandidacies = new ArrayCollection();
    }

    #[Groups(['api_candidacy_read'])]
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public static function createForHost(
        Committee $committee,
        Adherent $host,
        \DateTimeInterface $subscriptionDate,
    ): self {
        return static::createForAdherent($committee, $host, self::COMMITTEE_HOST, $subscriptionDate);
    }

    public static function createFollower(
        Committee $committee,
        Adherent $follower,
        CommitteeMembershipTriggerEnum $trigger,
    ): self {
        return static::createForAdherent($committee, $follower, self::COMMITTEE_FOLLOWER, new \DateTime(), $trigger);
    }

    /**
     * Creates a new membership relationship between an adherent and a committee.
     */
    public static function createForAdherent(
        Committee $committee,
        Adherent $adherent,
        string $privilege,
        \DateTimeInterface $subscriptionDate,
        ?CommitteeMembershipTriggerEnum $trigger = null,
    ): self {
        return new self(
            self::createUuid($adherent->getUuid(), $committee->getUuid()),
            $committee,
            $adherent,
            $privilege,
            $subscriptionDate,
            $trigger
        );
    }

    public static function checkPrivilege(string $privilege): void
    {
        if (!\in_array($privilege, self::PRIVILEGES, true)) {
            throw new \InvalidArgumentException(\sprintf('Invalid privilege %s', $privilege));
        }
    }

    /**
     * Returns whether or not this membership is a supervisor privileged membership.
     */
    public function isSupervisor(?bool $isProvisional = null): bool
    {
        return $this->getAdherent()->isSupervisorOf($this->getCommittee(), $isProvisional);
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
        return self::COMMITTEE_FOLLOWER === $this->privilege && !$this->isSupervisor();
    }

    public function getPrivilege(): string
    {
        return $this->privilege;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getFullName(): ?string
    {
        return $this->adherent?->getFullName();
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

    public function getCommitteeCandidacy(?CommitteeElection $election): ?CommitteeCandidacy
    {
        if (!$election) {
            return null;
        }

        foreach ($this->committeeCandidacies as $candidacy) {
            if ($candidacy->getCommitteeElection() === $election) {
                return $candidacy;
            }
        }

        return null;
    }

    public function getCandidacyForElection(CommitteeElection $election): ?CommitteeCandidacy
    {
        return $this->getCommitteeCandidacy($election);
    }

    public function addCommitteeCandidacy(CommitteeCandidacy $committeeCandidacy): void
    {
        if (
            !$this->committeeCandidacies->contains($committeeCandidacy)
            && null === $this->getCommitteeCandidacy($committeeCandidacy->getCommitteeElection())
        ) {
            $committeeCandidacy->setCommitteeMembership($this);
            $this->committeeCandidacies->add($committeeCandidacy);
        }
    }

    public function removeCommitteeCandidacy(CommitteeCandidacy $committeeCandidacy): void
    {
        $this->committeeCandidacies->removeElement($committeeCandidacy);
    }

    public function removeCommitteeCandidacyForElection(CommitteeElection $election): void
    {
        $candidacy = $this->getCommitteeCandidacy($election);

        if ($candidacy) {
            $this->removeCommitteeCandidacy($candidacy);
        }
    }

    #[Groups(['adherent_committees_modal'])]
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

    #[Groups(['export', 'adherent_committees_modal', 'profile_read'])]
    public function getSubscriptionDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->joinedAt->format(\DATE_RFC822), $this->joinedAt->getTimezone());
    }

    public function getJoinedAt(): \DateTimeInterface
    {
        return $this->joinedAt;
    }

    public function matches(Adherent $adherent, Committee $committee): bool
    {
        return $adherent->equals($this->getAdherent()) && $this->committee->getUuid()->equals($committee->getUuid());
    }

    private static function createUuid(UuidInterface $adherentUuid, UuidInterface $committeeUuid): UuidInterface
    {
        $key = sha1(\sprintf('%s|%s', $adherentUuid->toString(), $committeeUuid->toString()));

        return Uuid::uuid5(Uuid::NAMESPACE_OID, $key);
    }

    public function hasActiveCommitteeCandidacy(?bool $confirmed = null): bool
    {
        foreach ($this->committeeCandidacies as $candidacy) {
            if ($candidacy->isOngoing() && (!$confirmed || $candidacy->isConfirmed())) {
                return true;
            }
        }

        return false;
    }

    public function setTrigger(?CommitteeMembershipTriggerEnum $trigger): void
    {
        $this->trigger = $trigger;
    }

    public function isManual(): bool
    {
        return \in_array($this->trigger, [
            CommitteeMembershipTriggerEnum::MANUAL,
            CommitteeMembershipTriggerEnum::ADMIN,
        ], true);
    }
}
