<?php

namespace App\VotingPlatform\Election;

use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\Workflow\StateMachine;

class VoteCommandProcessor
{
    private $workflow;

    public function __construct(StateMachine $votingProcessWorkflow)
    {
        $this->workflow = $votingProcessWorkflow;
    }

    public function canStart(VoteCommand $voteCommand): bool
    {
        return $voteCommand->isStart() || $this->can($voteCommand, VoteCommandStateEnum::TO_START);
    }

    public function canVote(VoteCommand $voteCommand): bool
    {
        return $voteCommand->isVote() || $this->can($voteCommand, VoteCommandStateEnum::TO_VOTE);
    }

    public function canConfirm(VoteCommand $voteCommand): bool
    {
        return $voteCommand->isConfirm() || $this->can($voteCommand, VoteCommandStateEnum::TO_CONFIRM);
    }

    public function doStart(VoteCommand $command): void
    {
        if (!$command->isStart()) {
            $this->apply($command, VoteCommandStateEnum::TO_START);
        }
    }

    public function doVote(VoteCommand $command): void
    {
        if (!$command->isVote()) {
            $this->apply($command, VoteCommandStateEnum::TO_VOTE);
        }
    }

    public function doConfirm(VoteCommand $command)
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
