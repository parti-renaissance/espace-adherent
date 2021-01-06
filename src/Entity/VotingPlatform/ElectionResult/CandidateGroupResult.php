<?php

namespace App\Entity\VotingPlatform\ElectionResult;

use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\CandidateGroup;
use App\VotingPlatform\Designation\MajorityVoteMentionEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="voting_platform_candidate_group_result")
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

    /**
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $totalMentions;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $majorityMention;

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

    public function incrementMention(string $mention): void
    {
        $this->increment();

        if (null === $this->totalMentions) {
            $this->totalMentions = [];
        }

        if (!isset($this->totalMentions[$mention])) {
            $this->totalMentions[$mention] = 0;
        }

        ++$this->totalMentions[$mention];
    }

    public function getTotalMentions(): ?array
    {
        if ($this->totalMentions) {
            uksort($this->totalMentions, function (string $a, string $b) {
                return array_search($a, MajorityVoteMentionEnum::ALL) <=> array_search($b, MajorityVoteMentionEnum::ALL);
            });

            return $this->totalMentions;
        }

        return $this->totalMentions;
    }

    public function getMajorityMention(): ?string
    {
        return $this->majorityMention;
    }

    public function setMajorityMention(?string $majorityMention): void
    {
        $this->majorityMention = $majorityMention;
    }
}
