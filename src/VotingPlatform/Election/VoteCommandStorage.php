<?php

namespace App\VotingPlatform\Election;

use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class VoteCommandStorage
{
    public const SESSION_KEY_COMMAND = 'vote_process.command';
    public const SESSION_KEY_VOTER_KEY = 'vote_process.voter_key';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getVoteCommand(Election $election): VoteCommand
    {
        $command = $this->session->get(self::SESSION_KEY_COMMAND);

        return $command instanceof VoteCommand && $command->getElectionUuid() === $election->getUuid()->toString() ?
            $command : $this->createVoteCommand($election);
    }

    public function save(VoteCommand $voteCommand): void
    {
        $this->session->set(self::SESSION_KEY_COMMAND, $voteCommand);
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY_COMMAND);
    }

    private function createVoteCommand(Election $election): VoteCommand
    {
        return new VoteCommand($election);
    }

    public function saveLastVoterKey(string $voterKey): void
    {
        $this->session->set(self::SESSION_KEY_VOTER_KEY, $voterKey);
    }

    public function getLastVoterKey(): ?string
    {
        return $this->session->get(self::SESSION_KEY_VOTER_KEY);
    }
}
