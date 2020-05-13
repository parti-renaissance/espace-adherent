<?php

namespace App\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\CandidateGroupRepository")
 *
 * @ORM\Table(name="voting_platform_candidate_group")
 *
 * @Algolia\Index(autoIndex=false)
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
     * @var Election
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Election", inversedBy="candidateGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $election;

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

    public function setElection(Election $election): void
    {
        $this->election = $election;
    }

    /**
     * @return Candidate[]
     */
    public function getCandidates(): array
    {
        return $this->candidates->toArray();
    }
}
