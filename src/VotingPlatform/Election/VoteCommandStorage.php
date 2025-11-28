<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election;

use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\HttpFoundation\RequestStack;

class VoteCommandStorage
{
    public const SESSION_KEY_COMMAND = 'vote_process.command';
    public const SESSION_KEY_VOTER_KEY = 'vote_process.voter_key';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getVoteCommand(Election $election): VoteCommand
    {
        $command = $this->requestStack->getSession()->get(self::SESSION_KEY_COMMAND);

        return $command instanceof VoteCommand && $command->getElectionUuid() === $election->getUuid()->toString() ?
            $command : $this->createVoteCommand($election);
    }

    public function save(VoteCommand $voteCommand): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY_COMMAND, $voteCommand);
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY_COMMAND);
    }

    private function createVoteCommand(Election $election): VoteCommand
    {
        return new VoteCommand($election);
    }

    public function saveLastVoterKey(string $voterKey): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY_VOTER_KEY, $voterKey);
    }

    public function getLastVoterKey(): ?string
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY_VOTER_KEY);
    }
}
