<?php

namespace App\Twig;

use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\ElectionRepository;
use Twig\Extension\RuntimeExtensionInterface;

class VotingPlatformRuntime implements RuntimeExtensionInterface
{
    private $electionRepository;

    public function __construct(ElectionRepository $electionRepository)
    {
        $this->electionRepository = $electionRepository;
    }

    public function findElectionForCommittee(Committee $committee): ?Election
    {
        return $this->electionRepository->findOneForCommittee($committee);
    }
}
