<?php

namespace App\Twig;

use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Entity\TerritorialCouncil\ElectionPoll\Vote;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\TerritorialCouncil\ElectionPoll\Manager;
use Twig\Extension\RuntimeExtensionInterface;

class TerritorialCouncilRuntime implements RuntimeExtensionInterface
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function getElectionPollVote(Poll $poll, TerritorialCouncilMembership $membership): ?Vote
    {
        return $this->manager->findVote($poll, $membership);
    }
}
