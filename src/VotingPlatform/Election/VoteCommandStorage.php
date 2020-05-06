<?php

namespace App\VotingPlatform\Election;

use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\VoteCommand\CommitteeAdherentVoteCommand;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class VoteCommandStorage
{
    public const SESSION_KEY = 'vote_process';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getVoteCommand(Election $election): VoteCommand
    {
        $command = $this->session->get(self::SESSION_KEY);

        return $command instanceof VoteCommand ? $command : $this->createVoteCommand($election);
    }

    public function save(VoteCommand $voteCommand): void
    {
        $this->session->set(self::SESSION_KEY, $voteCommand);
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }

    private function createVoteCommand(Election $election): VoteCommand
    {
        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $election->getDesignationType()) {
            return new CommitteeAdherentVoteCommand($election);
        }

        return new VoteCommand($election);
    }
}
