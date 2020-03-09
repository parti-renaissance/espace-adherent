<?php

namespace AppBundle\Election;

use AppBundle\Entity\City;
use AppBundle\Entity\Election\BaseVoteResult;
use AppBundle\Entity\Election\CityVoteResult;
use AppBundle\Entity\Election\MinistryVoteResult;
use AppBundle\Entity\Election\VotePlaceResult;
use AppBundle\Entity\Election\VoteResultListCollection;
use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\VotePlace;
use AppBundle\Repository\Election\CityVoteResultRepository;
use AppBundle\Repository\Election\MinistryVoteResultRepository;
use AppBundle\Repository\Election\VotePlaceResultRepository;
use AppBundle\Repository\Election\VoteResultListCollectionRepository;
use AppBundle\Repository\ElectionRepository;
use Doctrine\ORM\EntityManagerInterface;

class ElectionManager
{
    private $entityManager;
    private $electionRepository;
    private $voteResultRepository;
    private $listCollectionRepository;
    private $ministryVoteResultRepository;
    private $cityVoteResultRepository;

    private $currentRound;

    public function __construct(
        EntityManagerInterface $entityManager,
        ElectionRepository $electionRepository,
        VotePlaceResultRepository $voteResultRepository,
        MinistryVoteResultRepository $ministryVoteResultRepository,
        CityVoteResultRepository $cityVoteResultRepository,
        VoteResultListCollectionRepository $listCollectionRepository
    ) {
        $this->entityManager = $entityManager;
        $this->electionRepository = $electionRepository;
        $this->voteResultRepository = $voteResultRepository;
        $this->ministryVoteResultRepository = $ministryVoteResultRepository;
        $this->cityVoteResultRepository = $cityVoteResultRepository;
        $this->listCollectionRepository = $listCollectionRepository;
    }

    public function getClosestElectionRound(): ?ElectionRound
    {
        if ($this->currentRound) {
            return $this->currentRound;
        }

        if (!$election = $this->electionRepository->findClosestElection()) {
            return null;
        }

        $now = new \DateTime();

        $selectedRound = $election->getRounds()->current();
        $days = $selectedRound->getDate()->diff($now)->days;

        foreach ($election->getRounds() as $round) {
            if (($tmp = $round->getDate()->diff($now)->days) < $days) {
                $selectedRound = $round;
                $days = $tmp;
            }
        }

        return $selectedRound;
    }

    public function getVotePlaceResultForCurrentElectionRound(
        VotePlace $votePlace,
        bool $updateLists = false
    ): ?VotePlaceResult {
        if (!$round = $this->getClosestElectionRound()) {
            return null;
        }

        $voteResult = $this->voteResultRepository->findOneForVotePlace($votePlace, $round) ?? new VotePlaceResult($votePlace, $round);

        if (false === $updateLists) {
            return $voteResult;
        }

        return $this->updateListCollection(
            $voteResult,
            $this->listCollectionRepository->findOneByCityInseeCode($votePlace->getInseeCode())
        );
    }

    public function getCityVoteResultForCurrentElectionRound(City $city, bool $updateLists = false): ?CityVoteResult
    {
        if (!$round = $this->getClosestElectionRound()) {
            return null;
        }

        $voteResult = $this->cityVoteResultRepository->findOneForCity($city, $round) ?? new CityVoteResult($city, $round);

        if (false === $updateLists) {
            return $voteResult;
        }

        return $this->updateListCollection(
            $voteResult,
            $this->listCollectionRepository->findOneByCity($city)
        );
    }

    public function getMinistryVoteResultForCurrentElectionRound(City $city): ?MinistryVoteResult
    {
        if (!$round = $this->getClosestElectionRound()) {
            return null;
        }

        return $this->ministryVoteResultRepository->findOneForCity($city, $round) ?? new MinistryVoteResult($city, $round);
    }

    private function updateListCollection(
        BaseVoteResult $voteResult,
        ?VoteResultListCollection $listsCollection
    ): BaseVoteResult {
        if ($listsCollection) {
            $voteResult->updateLists($listsCollection);

            if (!$voteResult->getId()) {
                $this->entityManager->persist($voteResult);
            }

            $this->entityManager->flush();
        }

        return $voteResult;
    }
}
