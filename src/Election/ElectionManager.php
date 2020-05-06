<?php

namespace App\Election;

use App\Entity\City;
use App\Entity\Election\BaseVoteResult;
use App\Entity\Election\CityVoteResult;
use App\Entity\Election\MinistryVoteResult;
use App\Entity\Election\VotePlaceResult;
use App\Entity\Election\VoteResultListCollection;
use App\Entity\ElectionRound;
use App\Entity\VotePlace;
use App\Repository\Election\CityVoteResultRepository;
use App\Repository\Election\MinistryVoteResultRepository;
use App\Repository\Election\VotePlaceResultRepository;
use App\Repository\Election\VoteResultListCollectionRepository;
use App\Repository\ElectionRepository;
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

        /** @var ElectionRound $selectedRound */
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

        return $this->getVotePlaceResult($round, $votePlace, $updateLists);
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
            $this->listCollectionRepository->findOneByCity($city, $round)
        );
    }

    public function getMinistryVoteResultForCurrentElectionRound(City $city): ?MinistryVoteResult
    {
        if (!$round = $this->getClosestElectionRound()) {
            return null;
        }

        return $this->ministryVoteResultRepository->findOneForCity($city, $round) ?? new MinistryVoteResult($city, $round);
    }

    public function getListCollectionForVotePlace(
        ElectionRound $electionRound,
        VotePlace $vote
    ): ?VoteResultListCollection {
        return $this->listCollectionRepository->findOneByCityInseeCode($vote->getInseeCode(), $electionRound);
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

    public function findElectionRound(int $id): ?ElectionRound
    {
        if (!$election = $this->electionRepository->findElectionOfRound($id)) {
            return null;
        }

        foreach ($election->getRounds() as $round) {
            if ($round->getId() === $id) {
                return $round;
            }
        }

        return null;
    }

    /**
     * @return MinistryVoteResult[]
     */
    public function getMunicipalMinistryResultsHistory(City $city): array
    {
        if (!$election = $this->electionRepository->getMunicipalElection2014()) {
            return [];
        }

        return $this->ministryVoteResultRepository->findAllForCity($city, $election->getRounds()->toArray());
    }

    public function getVotePlaceResult(
        ElectionRound $electionRound,
        VotePlace $votePlace,
        bool $updateLists = false
    ): VotePlaceResult {
        $voteResult = $this->voteResultRepository->findOneForVotePlace($votePlace, $electionRound) ??
            new VotePlaceResult($votePlace, $electionRound);

        if (false === $updateLists) {
            return $voteResult;
        }

        return $this->updateListCollection(
            $voteResult,
            $this->listCollectionRepository->findOneByCityInseeCode($votePlace->getInseeCode(), $electionRound)
        );
    }
}
