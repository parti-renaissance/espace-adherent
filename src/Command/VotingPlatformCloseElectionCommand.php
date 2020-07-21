<?php

namespace App\Command;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Repository\CommitteeElectionRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Events;
use App\VotingPlatform\Notifier\Event\CommitteeElectionCandidacyPeriodIsOverEvent;
use App\VotingPlatform\Notifier\Event\CommitteeElectionVoteIsOverEvent;
use App\VotingPlatform\VoteResult\VoteResultAggregator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    /** @var DesignationRepository */
    private $designationRepository;
    /** @var CommitteeElectionRepository */
    private $committeeElectionRepository;
    /** @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;
    /** @var EventDispatcherInterface */
    private $dispatcher;

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
        $this->closeElections();

        $this->notifyForEndForCandidacy();
    }

    private function closeElections(): void
    {
        $date = new \DateTime();

        $this->io->progressStart();

        while ($elections = $this->electionRepository->getElectionsToClose($date, 50)) {
            foreach ($elections as $election) {
                $this->doCloseElection($election);

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();
        }

        $this->io->progressFinish();
    }

    private function doCloseElection(Election $election): void
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

        $this->notifyEndOfElectionRound($election);

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

    private function notifyForEndForCandidacy(): void
    {
        $date = new \DateTime();

        $designations = $this->designationRepository->getWithFinishCandidacyPeriod($date);

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
                $this->notifyCommitteeElections($designation);
            }
        }

        $this->io->progressFinish();
    }

    public function notifyCommitteeElections(Designation $designation): void
    {
        while ($committeeElections = $this->committeeElectionRepository->findAllToNotify($designation)) {
            foreach ($committeeElections as $committeeElection) {
                $memberships = $this->committeeMembershipRepository->findVotingMemberships($committee = $committeeElection->getCommittee());

                foreach ($memberships as $membership) {
                    $this->dispatcher->dispatch(Events::CANDIDACY_PERIOD_CLOSE, new CommitteeElectionCandidacyPeriodIsOverEvent(
                        $membership->getAdherent(),
                        $designation,
                        $committee
                    ));
                }

                $committeeElection->setAdherentNotified(true);

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();
        }
    }

    private function notifyEndOfElectionRound(Election $election): void
    {
        $committee = $election->getElectionEntity()->getCommittee();

        $memberships = $this->committeeMembershipRepository->findVotingMemberships($committee);

        foreach ($memberships as $membership) {
            $this->dispatcher->dispatch(Events::VOTE_CLOSE, new CommitteeElectionVoteIsOverEvent(
                $membership->getAdherent(),
                $election->getDesignation(),
                $committee
            ));
        }
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

    /** @required */
    public function setDesignationRepository(DesignationRepository $designationRepository): void
    {
        $this->designationRepository = $designationRepository;
    }

    /** @required */
    public function setCommitteeElectionRepository(CommitteeElectionRepository $committeeElectionRepository): void
    {
        $this->committeeElectionRepository = $committeeElectionRepository;
    }

    /** @required */
    public function setCommitteeMembershipRepository(CommitteeMembershipRepository $committeeMembershipRepository): void
    {
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    /** @required */
    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
}
