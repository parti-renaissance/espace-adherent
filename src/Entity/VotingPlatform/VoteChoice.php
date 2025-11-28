<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'voting_platform_vote_choice')]
class VoteChoice
{
    public const BLANK_VOTE_VALUE = -1;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var VoteResult
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: VoteResult::class, inversedBy: 'voteChoices')]
    private $voteResult;

    /**
     * @var CandidateGroup
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CandidateGroup::class)]
    private $candidateGroup;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isBlank = false;

    /**
     * For storing the mention on Majority Vote
     *
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $mention;

    /**
     * @var ElectionPool
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectionPool::class)]
    private $electionPool;

    public function __construct(ElectionPool $electionPool)
    {
        $this->electionPool = $electionPool;
    }

    public function setVoteResult(VoteResult $voteResult): void
    {
        $this->voteResult = $voteResult;
    }

    public function setCandidateGroup(CandidateGroup $candidateGroup): void
    {
        $this->candidateGroup = $candidateGroup;
    }

    public function setIsBlank(bool $isBlank): void
    {
        $this->isBlank = $isBlank;
    }

    public function isBlank(): bool
    {
        return $this->isBlank;
    }

    public function getCandidateGroup(): ?CandidateGroup
    {
        return $this->candidateGroup;
    }

    public function getElectionPool(): ElectionPool
    {
        return $this->electionPool;
    }

    public function setMention(?string $mention): void
    {
        $this->mention = $mention;
    }

    public function getMention(): ?string
    {
        return $this->mention;
    }
}
