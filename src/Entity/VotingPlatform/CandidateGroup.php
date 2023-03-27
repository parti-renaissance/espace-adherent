<?php

namespace App\Entity\VotingPlatform;

use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\CandidateGroupRepository")
 * @ORM\Table(name="voting_platform_candidate_group")
 */
class CandidateGroup
{
    use EntityIdentityTrait;

    /**
     * @var Candidate[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\VotingPlatform\Candidate",
     *     cascade={"all"},
     *     mappedBy="candidateGroup",
     *     orphanRemoval=true
     * )
     */
    private $candidates;

    /**
     * @var ElectionPool
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\ElectionPool", inversedBy="candidateGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $electionPool;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Groups({"election_result"})
     */
    private $elected = false;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $mediaFilePath = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->candidates = new ArrayCollection();
    }

    public function addCandidate(Candidate $candidate): void
    {
        if (!$this->candidates->contains($candidate)) {
            $candidate->setCandidateGroup($this);
            $this->candidates->add($candidate);
        }
    }

    public function setElectionPool(ElectionPool $electionPool): void
    {
        $this->electionPool = $electionPool;
    }

    /**
     * @return Candidate[]
     */
    public function getCandidates(): array
    {
        return $this->candidates->filter(fn (Candidate $candidate) => !$candidate->isSubstitute)->toArray();
    }

    /**
     * @return Candidate[]
     *
     * @Groups({"election_result"})
     * @SerializedName("candidates")
     */
    public function getCandidatesSorted(bool $byPosition = false): array
    {
        $candidates = $this->getCandidates();

        usort($candidates, function (Candidate $a, Candidate $b) use ($byPosition) {
            return $byPosition ?
                $a->position <=> $b->position :
                $b->isFemale() <=> $a->isFemale();
        });

        return $candidates;
    }

    /**
     * @return Candidate[]
     */
    public function getSubstituteCandidates(): array
    {
        $candidates = $this->candidates->filter(fn (Candidate $candidate) => true === $candidate->isSubstitute)->toArray();

        usort($candidates, function (Candidate $a, Candidate $b) {
            return $a->position <=> $b->position;
        });

        return $candidates;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function isElected(): bool
    {
        return $this->elected;
    }

    public function setElected(bool $elected): void
    {
        $this->elected = $elected;
    }

    public function getGenders(): array
    {
        return array_map(function (Candidate $candidate) {
            return $candidate->getGender();
        }, $this->candidates->toArray());
    }

    public function getCandidateByGender(string $gender): ?Candidate
    {
        foreach ($this->candidates as $candidate) {
            if ($candidate->getGender() === $gender) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @Groups({"election_result"})
     */
    public function getTitle(): string
    {
        $labelParts = [];

        /** @var Candidate $first */
        $first = $this->candidates->first();

        $labelParts[] = $first->getFullName();

        if (($count = $this->candidates->count()) > 1) {
            $labelParts[] = sprintf('(+%d candidat%s)', $count - 1, $count > 2 ? 's' : '');
        }

        return implode(' ', $labelParts);
    }
}
