<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election;

use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\Workflow\WorkflowInterface;

class VoteCommandProcessor
{
    private WorkflowInterface $workflow;

    public function __construct(WorkflowInterface $votingProcessStateMachine)
    {
        $this->workflow = $votingProcessStateMachine;
    }

    public function canVote(VoteCommand $voteCommand): bool
    {
        return $voteCommand->isVote() || $this->can($voteCommand, VoteCommandStateEnum::TO_VOTE);
    }

    public function canConfirm(VoteCommand $voteCommand): bool
    {
        return $voteCommand->isConfirm() || $this->can($voteCommand, VoteCommandStateEnum::TO_CONFIRM);
    }

    public function canFinish(VoteCommand $voteCommand): bool
    {
        return $voteCommand->isFinish() || $this->can($voteCommand, VoteCommandStateEnum::TO_FINISH);
    }

    public function doVote(VoteCommand $command): void
    {
        if (!$command->isVote()) {
            $this->apply($command, VoteCommandStateEnum::TO_VOTE);
        }
    }

    public function doConfirm(VoteCommand $command): void
    {
        if (!$command->isConfirm()) {
            $this->apply($command, VoteCommandStateEnum::TO_CONFIRM);
        }
    }

    public function doFinish(VoteCommand $command): void
    {
        if (!$command->isFinish()) {
            $this->apply($command, VoteCommandStateEnum::TO_FINISH);
        }
    }

    private function can(VoteCommand $command, string $transitionName): bool
    {
        return $this->workflow->can($command, $transitionName);
    }

    private function apply(VoteCommand $command, string $transitionName): void
    {
        $this->workflow->apply($command, $transitionName);
    }
}
