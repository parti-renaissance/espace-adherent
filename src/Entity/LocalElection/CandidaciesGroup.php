<?php

declare(strict_types=1);

namespace App\Entity\LocalElection;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\VotingPlatform\Designation\BaseCandidaciesGroup;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'local_election_candidacies_group')]
class CandidaciesGroup extends BaseCandidaciesGroup implements \Stringable, EntityAdministratorBlameableInterface
{
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: LocalElection::class, inversedBy: 'candidaciesGroups')]
    public ?LocalElection $election = null;

    /**
     * @var CandidacyInterface[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'candidaciesGroup', targetEntity: Candidacy::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected $candidacies;

    /**
     * @var CandidacyInterface[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'candidaciesGroup', targetEntity: SubstituteCandidacy::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected $substituteCandidacies;

    #[ORM\Column(nullable: true)]
    public ?string $faithStatementFileName = null;

    #[Assert\File(maxSize: '5M', binaryFormat: false, mimeTypes: ['application/pdf'])]
    public ?UploadedFile $file = null;

    public function __construct()
    {
        parent::__construct();

        $this->substituteCandidacies = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->election;
    }

    public function hasFaithStatementFile(): bool
    {
        return null !== $this->faithStatementFileName;
    }

    public function getFaithStatementFilePath(): string
    {
        return \sprintf('elections/profession-de-foi/%s', $this->faithStatementFileName);
    }

    public function addCandidacy(CandidacyInterface $candidacy): void
    {
        if ($this->candidacies->isEmpty()) {
            $candidacy->setPosition(1);
        }

        $candidacy->election = $this->election;

        parent::addCandidacy($candidacy);
    }

    /**
     * @return CandidacyInterface[]
     */
    public function getSubstituteCandidacies(): array
    {
        return $this->substituteCandidacies->toArray();
    }

    public function addSubstituteCandidacy(CandidacyInterface $substitute): void
    {
        if ($this->substituteCandidacies->isEmpty()) {
            $substitute->setPosition(1);
        }

        $substitute->election = $this->election;

        if (!$this->substituteCandidacies->contains($substitute)) {
            $substitute->setCandidaciesGroup($this);
            $this->substituteCandidacies->add($substitute);
        }
    }

    public function removeSubstituteCandidacy(CandidacyInterface $substitute): void
    {
        $this->substituteCandidacies->removeElement($substitute);
        $substitute->setCandidaciesGroup(null);
    }
}
