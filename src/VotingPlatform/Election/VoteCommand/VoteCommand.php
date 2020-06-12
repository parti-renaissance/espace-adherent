<?php

namespace App\VotingPlatform\Election\VoteCommand;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\VoteChoice;
use App\VotingPlatform\Election\VoteCommandStateEnum;
use Symfony\Component\Validator\Constraints as Assert;

class VoteCommand
{
    /**
     * @var string
     */
    private $state = VoteCommandStateEnum::INITIALIZE;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $poolChoice;

    private $choicesByPools = [];

    /**
     * @var string
     */
    private $electionUuid;

    public function __construct(Election $election)
    {
        $this->electionUuid = $election->getUuid()->toString();
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getPoolChoice(): ?string
    {
        return $this->poolChoice;
    }

    public function setPoolChoice(string $poolChoice): void
    {
        $this->poolChoice = $poolChoice;
    }

    public function isStart(): bool
    {
        return VoteCommandStateEnum::START === $this->state;
    }

    public function isVote(): bool
    {
        return VoteCommandStateEnum::VOTE === $this->state;
    }

    public function isConfirm(): bool
    {
        return VoteCommandStateEnum::CONFIRM === $this->state;
    }

    public function isFinish(): bool
    {
        return VoteCommandStateEnum::FINISH === $this->state;
    }

    public function getElectionUuid(): string
    {
        return $this->electionUuid;
    }

    public function getCandidateGroupUuids(): array
    {
        return array_filter($this->choicesByPools, static function (string $value) {
            return VoteChoice::BLANK_VOTE_VALUE !== $value;
        });
    }

    public function getChoicesByPools(): array
    {
        return $this->choicesByPools;
    }

    /**
     * @param ElectionPool[] $pools
     */
    public function updateForCurrentPool(array $pools): ?ElectionPool
    {
        if (0 === \count($this->choicesByPools)) {
            return current($pools);
        }

        foreach ($pools as $pool) {
            if (!isset($this->choicesByPools[$pool->getId()])) {
                return $pool;
            }
        }

        // update selected candidate for current pool
        if (!empty($pool) && $this->choicesByPools[$pool->getId()]) {
            $this->poolChoice = $this->choicesByPools[$pool->getId()];
            unset($this->choicesByPools[$pool->getId()]);
        }

        return $pool ?? null;
    }

    public function updatePoolChoice(ElectionPool $pool): void
    {
        $this->choicesByPools[$pool->getId()] = $this->poolChoice;

        $this->poolChoice = null;
    }

    public function removeLastChoice(): self
    {
        $this->poolChoice = array_pop($this->choicesByPools);

        return $this;
    }
}
