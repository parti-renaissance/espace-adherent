<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\Validator\TerritorialCouncil\ValidTerritorialCouncilCandidacyInvitation;
use Doctrine\Common\Collections\Collection;
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
 *
 * @ValidTerritorialCouncilCandidacyInvitation(groups={"national_council_election"})
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
     * @var CandidacyInvitation[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TerritorialCouncil\CandidacyInvitation", mappedBy="candidacy", cascade={"all"})
     *
     * @Assert\NotBlank
     * @Assert\Count(value=1, groups={"copol_election"}, exactMessage="This value should not be blank.")
     * @Assert\Count(value=2, groups={"national_council_election"})
     * @Assert\Valid(groups={"copol_election", "national_council_election"})
     */
    protected $invitations;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $quality;

    /**
     * @var CandidaciesGroup|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\CandidaciesGroup", inversedBy="candidacies", cascade={"persist"})
     */
    protected $candidaciesGroup;

    public function __construct(
        TerritorialCouncilMembership $membership,
        Election $election,
        string $gender,
        ?UuidInterface $uuid = null
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

    public function isCouncilor(): bool
    {
        return
            \in_array($this->quality, [TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR, TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR], true)
            || (
                TerritorialCouncilQualityEnum::CITY_COUNCILOR === $this->quality
                && str_starts_with($this->membership->getQualitiesWithZones()[$this->quality], 'Paris ')
            );
    }

    public function getType(): string
    {
        return self::TYPE_TERRITORIAL_COUNCIL;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->membership->getAdherent();
    }

    protected function createCandidaciesGroup(): BaseCandidaciesGroup
    {
        return new CandidaciesGroup();
    }
}
