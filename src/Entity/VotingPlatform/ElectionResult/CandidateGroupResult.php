<?php

namespace App\Entity\VotingPlatform\ElectionResult;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\CandidateGroup;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="voting_platform_candidate_group_result")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CandidateGroupResult
{
    use EntityIdentityTrait;

    /**
     * @var CandidateGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\CandidateGroup")
     */
    private $candidateGroup;

    /**
     * @var ElectionPoolResult
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\ElectionResult\ElectionPoolResult", inversedBy="candidateGroupResults")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $electionPoolResult;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private $total = 0;

    public function __construct(CandidateGroup $candidateGroup, UuidInterface $uuid = null)
    {
        $this->candidateGroup = $candidateGroup;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function increment(): void
    {
        ++$this->total;
    }

    public function getCandidateGroup(): CandidateGroup
    {
        return $this->candidateGroup;
    }

    public function setElectionPoolResult(ElectionPoolResult $electionPoolResult): void
    {
        $this->electionPoolResult = $electionPoolResult;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getRate(): int
    {
        return $this->electionPoolResult->getExpressed() < 1 ? 0 :
            $this->total * 100 / $this->electionPoolResult->getExpressed();
    }
}
