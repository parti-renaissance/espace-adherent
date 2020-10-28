<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
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
     * @var Election
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\Election")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $election;

    /**
     * @var TerritorialCouncilMembership
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilMembership", inversedBy="candidacies")
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
    protected $invitation;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $quality;

    /**
     * @var Candidacy|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\Candidacy", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $binome;

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

    public function updateFromBinome(): void
    {
        if ($this->binome) {
            $this->quality = $this->binome->getQuality();
            $this->faithStatement = $this->binome->getFaithStatement();
            $this->isPublicFaithStatement = $this->binome->isPublicFaithStatement();
        }
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
