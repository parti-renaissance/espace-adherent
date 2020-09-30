<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\CandidacyRepository")
 * @ORM\Table(name="territorial_council_candidacy")
 *
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 *
 * @Assert\Expression("(this.hasImageName() && !this.isRemoveImage()) || this.getImage()", message="Photo est obligatoire")
 */
class Candidacy extends BaseCandidacy
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=2000)
     */
    private $faithStatement;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isPublicFaithStatement = false;

    /**
     * @var Election
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\Election")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $election;

    /**
     * @var TerritorialCouncilMembership
     *
     * @ORM\ManyToOne(targetEntity="TerritorialCouncilMembership", inversedBy="candidacies")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $membership;

    /**
     * @var CandidacyInvitation|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\CandidacyInvitation", inversedBy="candidacy", cascade={"all"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid(groups={"invitation_edit"})
     */
    private $invitation;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $quality;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = CandidacyInterface::STATUS_DRAFT;

    /**
     * @var Candidacy|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\Candidacy", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $binome;

    /**
     * Helps to render two or single candidate
     *
     * @var bool
     */
    private $taken = false;

    public function __construct(
        TerritorialCouncilMembership $membership,
        Election $election,
        string $gender,
        UuidInterface $uuid = null
    ) {
        parent::__construct($gender, $uuid);

        $this->membership = $membership;
        $this->election = $election;
    }

    public function getElection(): ElectionEntityInterface
    {
        return $this->election;
    }

    public function setElection(Election $election): void
    {
        $this->election = $election;
    }

    public function getMembership(): ?TerritorialCouncilMembership
    {
        return $this->membership;
    }

    public function setMembership(TerritorialCouncilMembership $membership): void
    {
        $this->membership = $membership;
    }

    public function isOngoing(): bool
    {
        return $this->election->isOngoing();
    }

    public function getFaithStatement(): ?string
    {
        return $this->faithStatement;
    }

    public function setFaithStatement(?string $faithStatement): void
    {
        $this->faithStatement = $faithStatement;
    }

    public function isPublicFaithStatement(): bool
    {
        return $this->isPublicFaithStatement;
    }

    public function setIsPublicFaithStatement(bool $isPublicFaithStatement): void
    {
        $this->isPublicFaithStatement = $isPublicFaithStatement;
    }

    public function hasInvitation(): bool
    {
        return null !== $this->invitation;
    }

    public function hasPendingInvitation(): bool
    {
        return null !== $this->invitation && $this->invitation->isPending();
    }

    public function getInvitation(): ?CandidacyInvitation
    {
        return $this->invitation;
    }

    public function setInvitation(?CandidacyInvitation $invitation): void
    {
        $this->invitation = $invitation;

        if ($invitation) {
            $invitation->setCandidacy($this);
        }
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function getQualityPriority(): int
    {
        return TerritorialCouncilQualityEnum::QUALITY_PRIORITIES[$this->quality] ?? 0;
    }

    public function setQuality(string $quality): void
    {
        $this->quality = $quality;
    }

    public function isDraft(): bool
    {
        return CandidacyInterface::STATUS_DRAFT === $this->status;
    }

    public function isConfirmed(): bool
    {
        return CandidacyInterface::STATUS_CONFIRMED === $this->status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function updateFromBinome(): void
    {
        if ($this->binome) {
            $this->quality = $this->binome->getQuality();
            $this->faithStatement = $this->binome->getFaithStatement();
            $this->isPublicFaithStatement = $this->binome->isPublicFaithStatement();
        }
    }

    public function getBinome(): ?Candidacy
    {
        return $this->binome;
    }

    public function setBinome(Candidacy $candidacy): void
    {
        $this->binome = $candidacy;
    }

    public function confirm(): void
    {
        $this->status = CandidacyInterface::STATUS_CONFIRMED;
    }

    /**
     * @Assert\IsTrue(groups={"accept_invitation"})
     */
    public function isValidForConfirmation(): bool
    {
        return $this->binome && $this->binome->getInvitation() && $this->binome->isDraft();
    }

    public function isCouncilor(): bool
    {
        return
            \in_array($this->quality, [TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR, TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR], true)
            || (
                TerritorialCouncilQualityEnum::CITY_COUNCILOR === $this->quality
                && 0 === strpos($this->membership->getQualitiesWithZones()[$this->quality], 'Paris ')
            )
        ;
    }

    public function isTaken(): bool
    {
        return $this->taken;
    }

    public function take(): void
    {
        $this->taken = true;
    }

    public function getType(): string
    {
        return self::TYPE_TERRITORIAL_COUNCIL;
    }

    public function getAdherent(): Adherent
    {
        return $this->membership->getAdherent();
    }
}
