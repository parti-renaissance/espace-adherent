<?php

namespace App\VotingPlatform\Election;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use App\Repository\VotingPlatform\VoterRepository;
use Doctrine\ORM\EntityManagerInterface;

class VotersListManager
{
    private $entityManager;
    private $voterRepository;

    public function __construct(EntityManagerInterface $entityManager, VoterRepository $voterRepository)
    {
        $this->entityManager = $entityManager;
        $this->voterRepository = $voterRepository;
    }

    public function addToElection(Election $election, Adherent $adherent): Voter
    {
        $list = $election->getVotersList();

        $voter = $this->voterRepository->findForAdherent($adherent) ?? new Voter($adherent);

        if (!$this->voterRepository->existsForElection($adherent, $election->getUuid()->toString())) {
            $list->addVoter($voter);

            $this->entityManager->persist($voter);
            $this->entityManager->flush();
        }

        return $voter;
    }
}
