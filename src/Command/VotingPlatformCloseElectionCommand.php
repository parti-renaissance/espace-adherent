<?php

namespace App\Command;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\VoteResult\VoteResultAggregator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class VotingPlatformCloseElectionCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:close-election';

    /** @var SymfonyStyle */
    private $io;
    /** @var ElectionRepository */
    private $electionRepository;
    /** @var VoteResultAggregator */
    private $resultAggregator;
    /** @var EntityManagerInterface */
    private $entityManager;

    protected function configure()
    {
        $this->setDescription('Voting Platform: close election');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();

        $this->io->progressStart();

        while ($elections = $this->electionRepository->getElectionsToClose($date)) {
            foreach ($elections as $election) {
                $this->closeElection($election);

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();
        }

        $this->io->progressFinish();
    }

    private function closeElection(Election $election): void
    {
        $candidatesGroupResults = $this->resultAggregator->getResults($election)['aggregated']['candidates'];
        $currentRound = $election->getCurrentRound();

        $secondRoundPools = [];

        foreach ($currentRound->getElectionPools() as $pool) {
            $winners = $this->findElected($pool->getCandidateGroups(), $candidatesGroupResults);

            if (1 === \count($winners)) {
                current($winners)->setElected(true);
            } else {
                $secondRoundPools[] = $pool;
            }
        }

        if (empty($secondRoundPools) || $election->getSecondRoundEndDate()) {
            $election->close();
        } else {
            $election->startSecondRound($secondRoundPools);
        }

        $this->entityManager->flush();
    }

    /**
     * @return CandidateGroup[]
     */
    private function findElected(array $candidateGroups, array $results): array
    {
        if (empty($results)) {
            return [];
        }

        $uuids = array_map(function (CandidateGroup $group) {
            return $group->getUuid()->toString();
        }, $candidateGroups);

        $resultsForCurrentGroups = array_intersect_key($results, array_flip($uuids));

        $maxScore = max($resultsForCurrentGroups);
        $winnerUuids = array_keys(array_filter($resultsForCurrentGroups, function (int $score) use ($maxScore) {
            return $score === $maxScore;
        }));

        return array_filter($candidateGroups, function (CandidateGroup $group) use ($winnerUuids) {
            return \in_array($group->getUuid()->toString(), $winnerUuids);
        });
    }

    /** @required */
    public function setResultAggregator(VoteResultAggregator $resultAggregator): void
    {
        $this->resultAggregator = $resultAggregator;
    }

    /** @required */
    public function setElectionRepository(ElectionRepository $electionRepository): void
    {
        $this->electionRepository = $electionRepository;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
