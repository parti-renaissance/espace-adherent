<?php

namespace App\Entity\VotingPlatform\ElectionResult;

use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\CandidateGroup;
use App\VotingPlatform\Designation\MajorityVoteMentionEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'voting_platform_candidate_group_result')]
class CandidateGroupResult
{
    use EntityIdentityTrait;

    /**
     * @var CandidateGroup
     */
    #[Groups(['election_result'])]
    #[ORM\ManyToOne(targetEntity: CandidateGroup::class)]
    private $candidateGroup;

    /**
     * @var ElectionPoolResult
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectionPoolResult::class, inversedBy: 'candidateGroupResults')]
    private $electionPoolResult;

    /**
     * @var int
     */
    #[Groups(['election_result'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private $total = 0;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private $totalMentions;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $majorityMention;

    public function __construct(CandidateGroup $candidateGroup, ?UuidInterface $uuid = null)
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

    #[Groups(['election_result'])]
    public function getRate(bool $withBlank = false): float
    {
        $totalExpressed = $this->electionPoolResult->getExpressed($withBlank);
        if ($totalExpressed > 0) {
            return round($this->total * 100.0 / $totalExpressed, 2);
        }

        return 0;
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
