<?php

namespace App\Entity\VotingPlatform;

use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\CandidateGroupRepository")
 *
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
     */
    private $elected = false;

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
        return $this->candidates->toArray();
    }

    /**
     * @return Candidate[]
     */
    public function getCandidatesSorted(): array
    {
        $candidates = $this->candidates->toArray();

        usort($candidates, function (Candidate $a, Candidate $b) {
            return $b->isFemale() <=> $a->isFemale();
        });

        return $candidates;
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
}
