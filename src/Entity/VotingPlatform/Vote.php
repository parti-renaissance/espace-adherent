<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform;

use App\Repository\VotingPlatform\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\Table(name: 'voting_platform_vote')]
#[ORM\UniqueConstraint(name: 'unique_vote', columns: ['voter_id', 'election_round_id'])]
class Vote
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var Voter
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Voter::class, cascade: ['all'])]
    private $voter;

    /**
     * @var ElectionRound
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectionRound::class)]
    private $electionRound;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $votedAt;

    public function __construct(Voter $voter, ElectionRound $electionRound)
    {
        $this->voter = $voter;
        $this->electionRound = $electionRound;
        $this->votedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVotedAt(): \DateTime
    {
        return $this->votedAt;
    }

    public function getVoter(): Voter
    {
        return $this->voter;
    }

    public function getElectionRound(): ElectionRound
    {
        return $this->electionRound;
    }

    public function getElection(): Election
    {
        return $this->electionRound->getElection();
    }
}
