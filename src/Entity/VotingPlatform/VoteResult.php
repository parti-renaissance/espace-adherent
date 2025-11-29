<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform;

use App\Repository\VotingPlatform\VoteResultRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteResultRepository::class)]
#[ORM\Table(name: 'voting_platform_vote_result')]
#[ORM\UniqueConstraint(name: 'unique_vote', columns: ['voter_key', 'election_round_id'])]
class VoteResult
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column]
    private $voterKey;

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

    /**
     * @var VoteChoice[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'voteResult', targetEntity: VoteChoice::class, cascade: ['all'])]
    private $voteChoices;

    #[ORM\Column(nullable: true)]
    private ?string $zoneCode;

    public function __construct(ElectionRound $electionRound, string $voterKey, ?string $zoneCode)
    {
        $this->electionRound = $electionRound;
        $this->voterKey = $voterKey;
        $this->zoneCode = $zoneCode;
        $this->votedAt = new \DateTime();

        $this->voteChoices = new ArrayCollection();
    }

    public function addVoteChoice(VoteChoice $choice): void
    {
        if (!$this->voteChoices->contains($choice)) {
            $choice->setVoteResult($this);
            $this->voteChoices->add($choice);
        }
    }

    /**
     * @return VoteChoice[]
     */
    public function getVoteChoices(): array
    {
        return $this->voteChoices->toArray();
    }

    public function getVoterKey(): string
    {
        return $this->voterKey;
    }

    public static function generateVoterKey(): string
    {
        $matches = [];
        preg_match('/([[:alnum:]]{3})([[:alnum:]]{4})([[:alnum:]]{3})/i', bin2hex(random_bytes(5)), $matches);
        array_shift($matches);

        return implode('-', $matches);
    }

    public function findVoteChoiceForCandidateGroup(CandidateGroup $candidateGroup): ?VoteChoice
    {
        foreach ($this->voteChoices as $voteChoice) {
            if ($voteChoice->getCandidateGroup() === $candidateGroup) {
                return $voteChoice;
            }
        }

        return null;
    }
}
