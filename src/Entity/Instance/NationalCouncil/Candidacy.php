<?php

namespace App\Entity\Instance\NationalCouncil;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\Repository\Instance\NationalCouncil\CandidacyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'national_council_candidacy')]
#[ORM\Entity(repositoryClass: CandidacyRepository::class)]
class Candidacy extends BaseCandidacy
{
    /**
     * @var Election
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Election::class, inversedBy: 'candidacies')]
    private $election;

    /**
     * @var CandidaciesGroup|null
     */
    #[ORM\ManyToOne(targetEntity: CandidaciesGroup::class, cascade: ['persist'], inversedBy: 'candidacies')]
    protected $candidaciesGroup;

    /**
     * @var Adherent
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, fetch: 'EAGER')]
    private $adherent;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $quality;

    public function __construct(Election $election, Adherent $adherent)
    {
        parent::__construct($adherent->getGender());

        $this->election = $election;
        $this->adherent = $adherent;
    }

    protected function createCandidaciesGroup(): BaseCandidaciesGroup
    {
        return new CandidaciesGroup();
    }

    public function getType(): string
    {
        return self::TYPE_NATIONAL_COUNCIL;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getElection(): ElectionEntityInterface
    {
        return $this->election;
    }

    public function setQuality(?string $quality): void
    {
        $this->quality = $quality;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }
}
