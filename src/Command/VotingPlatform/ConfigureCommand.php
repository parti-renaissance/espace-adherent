<?php

namespace App\Command\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\LocalElection\LocalElection;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionPoolCodeEnum;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VotersList;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeElectionRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\Enum\ElectionCancelReasonEnum;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOpenEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:voting-platform:step-2:configure',
    description: 'Voting Platform: step 2: create Election and voters/candidates lists',
)]
class ConfigureCommand extends Command
{
    private ?SymfonyStyle $io = null;

    private CommitteeElectionRepository $committeeElectionRepository;
    private ElectionRepository $electionRepository;
    private DesignationRepository $designationRepository;
    private AdherentRepository $adherentRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct();

        $this->committeeElectionRepository = $this->entityManager->getRepository(CommitteeElection::class);
        $this->electionRepository = $this->entityManager->getRepository(Election::class);
        $this->designationRepository = $this->entityManager->getRepository(Designation::class);
        $this->adherentRepository = $this->entityManager->getRepository(Adherent::class);
    }

    protected function configure(): void
    {
        $this
            ->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Interval in minutes for designation selection (1 min by default)', 1)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime();

        $designations = $this->designationRepository->getIncomingDesignations(
            $date->modify(\sprintf('+%d minutes', (int) $input->getOption('interval')))
        );

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if ($designation->isCommitteeSupervisorType()) {
                $this->configureCommitteeSupervisorElections($designation);
            } elseif ($designation->isCommitteeTypes()) {
                $this->configureCommitteeElections($designation);
            } elseif ($designation->isPollType()) {
                $this->configurePoll($designation);
            } elseif ($designation->isLocalElectionType()) {
                $this->configureLocalElection($designation);
            } elseif ($designation->isLocalPollType()) {
                $this->configureLocalPoll($designation);
            } elseif ($designation->isConsultationType() || $designation->isVoteType()) {
                $this->configureConsultation($designation);
            } elseif ($designation->isTerritorialAssemblyType()) {
                $this->configureTerritorialAssembly($designation);
            } elseif ($designation->isCongressCNType()) {
                $this->configureCongressCN($designation);
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function configureCommitteeSupervisorElections(Designation $designation): void
    {
        $offset = 0;

        $timeToCheck = new \DateTime('+10 minutes');

        while ($committeeElections = $this->committeeElectionRepository->findAllByDesignation($designation, $offset)) {
            foreach ($committeeElections as $committeeElection) {
                $committee = $committeeElection->getCommittee();

                if (!$election = $this->electionRepository->findOneForCommittee($committee, $designation)) {
                    $election = $this->createNewElection($designation, $electionEntity = new ElectionEntity());
                    $electionEntity->setCommittee($committee);
                    $this->configureNewElectionForCommitteeSupervisor($election);
                }

                if ($election->isCanceled()) {
                    continue;
                }

                if (null === $election->getElectionEntity()->getCommittee()) {
                    $election->cancel(ElectionCancelReasonEnum::CommitteeMissing);
                    $this->entityManager->flush();

                    continue;
                }

                if ($designation->getVoteStartDate() < $timeToCheck) {
                    if (!$election->countCandidateGroups() && 0 === \count($committeeElection->getCandidacies())) {
                        $election->cancel(ElectionCancelReasonEnum::CandidatesMissing);
                        $this->entityManager->flush();

                        continue;
                    }

                    if (0 === $election->getVotersList()->countVoters()) {
                        $election->cancel(ElectionCancelReasonEnum::VotersMissing);
                        $this->entityManager->flush();

                        continue;
                    }

                    $this->configureCandidatesGroupsForCommitteeSupervisorElection($committeeElection, $election);
                }

                if ($election->isVotePeriodStarted() && !$election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED)) {
                    $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
                }
            }

            $offset += \count($committeeElections);
        }
    }

    private function configureCommitteeElections(Designation $designation): void
    {
        $offset = 0;

        while ($committeeElections = $this->committeeElectionRepository->findAllByDesignation($designation, $offset)) {
            foreach ($committeeElections as $committeeElection) {
                $committee = $committeeElection->getCommittee();

                if ($this->electionRepository->hasElectionForCommittee($committee, $designation)) {
                    continue;
                }

                if (!$this->isValidCommitteeElection($committeeElection, $designation)) {
                    continue;
                }

                $election = $this->createNewElection($designation, $electionEntity = new ElectionEntity());
                $electionEntity->setCommittee($committee);

                $this->configureNewElectionForCommittee($election);
            }

            $offset += \count($committeeElections);
        }
    }

    private function configurePoll(Designation $designation): void
    {
        if ($this->electionRepository->findOneByDesignation($designation)) {
            return;
        }

        $election = $this->createNewElection($designation);

        for ($i = 1; $i <= 3; ++$i) {
            $election->getCurrentRound()->addElectionPool($pool = new ElectionPool(
                'vote-statuses-resolution-title-'.$i
            ));
            $election->addElectionPool($pool);

            $pool->addCandidateGroup($group = new CandidateGroup());
            $group->addCandidate(new Candidate('Oui', '', ''));

            $pool->addCandidateGroup($group = new CandidateGroup());
            $group->addCandidate(new Candidate('Non', '', ''));
        }

        $this->entityManager->persist($list = $this->createVoterList($election, []));
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        $this->adherentRepository->associateWithVoterList($designation, $list);

        $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
    }

    private function configureLocalElection(Designation $designation): void
    {
        if (!$localElection = $this->entityManager->getRepository(LocalElection::class)->findByDesignation($designation)) {
            return;
        }

        $election = $this->electionRepository->findOneByDesignation($designation);

        if ($election) {
            if ($election->isVotePeriodStarted() && !$election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED)) {
                $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
            }

            return;
        }

        $lists = $localElection->getCandidaciesGroups();

        if (0 === \count($lists) || 0 === $localElection->countCandidacies()) {
            return;
        }

        $election = $this->createNewElection($designation);
        $election->getCurrentRound()->addElectionPool($pool = new ElectionPool($designation->getType()));
        $election->addElectionPool($pool);

        foreach ($lists as $list) {
            $pool->addCandidateGroup($group = new CandidateGroup());

            if ($list->hasFaithStatementFile()) {
                $group->mediaFilePath = $list->getFaithStatementFilePath();
            }

            $candidacyCount = \count($list->getCandidacies());

            foreach ($list->getCandidacies() as $candidacy) {
                $group->addCandidate($candidate = new Candidate(
                    $candidacy->getFirstName(),
                    $candidacy->getLastName(),
                    $candidacy->getGender()
                ));
                $candidate->position = $candidacy->getPosition();
            }

            foreach ($list->getSubstituteCandidacies() as $substituteCandidacy) {
                $group->addCandidate($candidate = new Candidate(
                    $substituteCandidacy->getFirstName(),
                    $substituteCandidacy->getLastName(),
                    $substituteCandidacy->getGender()
                ));
                $candidate->position = $candidacyCount + $substituteCandidacy->getPosition();
                $candidate->isSubstitute = true;
            }
        }

        $this->entityManager->persist($this->createVoterList(
            $election,
            $this->adherentRepository->findForLocalElection($designation->getZones()->toArray(), $designation->getElectionCreationDate() ?? $designation->getVoteStartDate()),
        ));
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        if ($election->isVotePeriodStarted()) {
            $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
        }
    }

    private function configureLocalPoll(Designation $designation): void
    {
        if ($election = $this->electionRepository->findOneByDesignation($designation)) {
            if (
                !$election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED)
                && $election->isVotePeriodStarted()
            ) {
                $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
            }

            return;
        }

        if (!$designation->poll || !$designation->poll->getQuestions()) {
            return;
        }

        $election = $this->createNewElection($designation);

        foreach ($designation->poll->getQuestions() as $question) {
            $election->getCurrentRound()->addElectionPool($pool = new ElectionPool($question->content, $question->description));
            $election->addElectionPool($pool);

            foreach ($question->getChoices() as $choice) {
                $pool->addCandidateGroup($group = new CandidateGroup());
                $group->setLabel($choice->label);
                $group->addCandidate(new Candidate($choice->label, '', ''));
            }
        }

        $this->entityManager->persist($this->createVoterList(
            $election,
            $this->adherentRepository->findForLocalElection($designation->getZones()->toArray(), $designation->getElectionCreationDate() ?? $designation->getVoteStartDate()),
        ));
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        if ($election->isVotePeriodStarted()) {
            $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
        }
    }

    private function configureConsultation(Designation $designation): void
    {
        if ($election = $this->electionRepository->findOneByDesignation($designation)) {
            if (
                !$election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED)
                && $election->isVotePeriodStarted()
            ) {
                $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
            }

            return;
        }

        if (!$designation->poll || !$designation->poll->getQuestions()) {
            return;
        }

        $election = $this->createNewElection($designation);

        foreach ($designation->poll->getQuestions() as $question) {
            $election->getCurrentRound()->addElectionPool($pool = new ElectionPool($question->content, $question->description));
            $election->addElectionPool($pool);

            foreach ($question->getChoices() as $choice) {
                $pool->addCandidateGroup($group = new CandidateGroup());
                $group->setLabel($choice->label);
                $group->addCandidate(new Candidate($choice->label, '', ''));
            }
        }

        $this->entityManager->persist($this->createVoterList($election, $designation->targetYear ? $this->adherentRepository->findAllForConsultation($designation->targetYear, $designation->getZones()->toArray()) : []));
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        if ($election->isVotePeriodStarted()) {
            $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
        }
    }

    private function configureTerritorialAssembly(Designation $designation): void
    {
        if ($election = $this->electionRepository->findOneByDesignation($designation)) {
            if (
                !$election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED)
                && $election->isVotePeriodStarted()
            ) {
                $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
            }

            return;
        }

        if (!\count($designation->getCandidacyPools())) {
            return;
        }

        $election = $this->createNewElection($designation);

        foreach ($designation->getCandidacyPools() as $candidacyPool) {
            $election->getCurrentRound()->addElectionPool($pool = new ElectionPool(''));
            $election->addElectionPool($pool);

            foreach ($candidacyPool->getCandidaciesGroups() as $candidaciesGroup) {
                $pool->addCandidateGroup($group = new CandidateGroup());

                foreach ($candidaciesGroup->getCandidacies() as $candidacy) {
                    $group->addCandidate(new Candidate($candidacy->getFirstName(), $candidacy->getLastName(), $candidacy->getGender(), null, null, $candidacy->isSubstitute));
                }
            }
        }

        $adherents = $this->adherentRepository->findAllWithActifLocalMandates();

        $this->entityManager->persist($this->createVoterList($election, $adherents));
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        if ($election->isVotePeriodStarted()) {
            $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
        }
    }

    private function configureCongressCN(Designation $designation): void
    {
        if ($election = $this->electionRepository->findOneByDesignation($designation)) {
            if (
                !$election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED)
                && $election->isVotePeriodStarted()
            ) {
                $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
            }

            return;
        }

        if (!\count($designation->getCandidacyPools())) {
            return;
        }

        $election = $this->createNewElection($designation);

        foreach ($designation->getCandidacyPools() as $candidacyPool) {
            $election->getCurrentRound()->addElectionPool($pool = new ElectionPool(''));
            $election->addElectionPool($pool);

            foreach ($candidacyPool->getCandidaciesGroups() as $candidaciesGroup) {
                $pool->addCandidateGroup($group = new CandidateGroup());
                $group->setLabel($candidaciesGroup->label);

                foreach ($candidaciesGroup->getCandidacies() as $candidacy) {
                    $group->addCandidate(new Candidate($candidacy->getFirstName(), $candidacy->getLastName(), $candidacy->getGender(), null, null, $candidacy->isSubstitute));
                }
            }
        }

        $adherents = $this->adherentRepository->findAllForCongressCNElection();

        $this->entityManager->persist($this->createVoterList($election, $adherents));
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        if ($election->isVotePeriodStarted()) {
            $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
        }
    }

    private function configureNewElectionForCommitteeSupervisor(Election $election): void
    {
        $designation = $election->getDesignation();

        if (!$designation->isCommitteeSupervisorType()) {
            return;
        }

        $committee = $election->getElectionEntity()->getCommittee();

        $memberships = $this->entityManager->getRepository(CommitteeMembership::class)->findVotingForElectionMemberships(
            $committee,
            $designation,
            false
        );

        $list = $this->createVoterList(
            $election,
            array_map(fn (CommitteeMembership $membership) => $membership->getAdherent(), $memberships)
        );

        $election->setVotersList($list);

        $this->entityManager->persist($list);
        $this->entityManager->persist($election);
        $this->entityManager->flush();
    }

    private function configureCandidatesGroupsForCommitteeSupervisorElection(
        CommitteeElection $committeeElection,
        Election $election,
    ): void {
        $designation = $election->getDesignation();

        if (!$designation->isCommitteeSupervisorType()) {
            return;
        }

        $pools = $election->getElectionPools() ?: [
            new ElectionPool(ElectionPoolCodeEnum::COMMITTEE_SUPERVISOR),
        ];

        $pool = current($pools);

        if ($pool->countCandidateGroups()) {
            return;
        }

        foreach ($committeeElection->getCandidaciesGroups() as $candidaciesGroup) {
            if (empty($candidaciesGroup->getCandidacies())) {
                continue;
            }

            $pool->addCandidateGroup($group = new CandidateGroup());
            foreach ($candidaciesGroup->getCandidacies() as $candidacy) {
                $group->addCandidate($this->createCommitteeSupervisorCandidate($candidacy));
            }
        }

        $electionRound = $election->getCurrentRound();

        foreach ($pools as $pool) {
            if ($pool->getCandidateGroups()) {
                $electionRound->addElectionPool($pool);
                $election->addElectionPool($pool);
            }
        }

        $this->entityManager->flush();
    }

    private function configureNewElectionForCommittee(Election $election): void
    {
        $electionRound = $election->getCurrentRound();
        $committee = $election->getElectionEntity()->getCommittee();

        // Create candidates groups
        $candidacies = $this->entityManager->getRepository(CommitteeCandidacy::class)->findConfirmedByCommittee($committee, $designation = $election->getDesignation());
        $pools = [];

        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
            $pools = [
                $femalePool = new ElectionPool(ElectionPoolCodeEnum::FEMALE),
                $malePool = new ElectionPool(ElectionPoolCodeEnum::MALE),
            ];

            foreach ($candidacies as $candidacy) {
                $group = new CandidateGroup();
                $group->addCandidate($candidate = new Candidate(
                    $candidacy->getFirstName(),
                    $candidacy->getLastName(),
                    $candidacy->getGender(),
                    $candidacy->getAdherent()
                ));

                $candidate->setImagePath($candidacy->getImagePath());
                $candidate->setBiography($candidacy->getBiography());

                if ($candidate->isFemale()) {
                    $femalePool->addCandidateGroup($group);
                } else {
                    $malePool->addCandidateGroup($group);
                }
            }
        }

        foreach ($pools as $pool) {
            if ($pool->getCandidateGroups()) {
                $electionRound->addElectionPool($pool);
                $election->addElectionPool($pool);
            }
        }

        $memberships = $this->entityManager->getRepository(CommitteeMembership::class)->findVotingForElectionMemberships($committee, $designation);

        $list = $this->createVoterList(
            $election,
            array_map(function (CommitteeMembership $membership) { return $membership->getAdherent(); }, $memberships)
        );

        // Mark as Ghost voter adherent who can vote in many committees
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
            foreach ($list->getVoters() as $voter) {
                if (\count($voter->getVotersListsForDesignation($designation)) > 1) {
                    $voter->setIsGhost(true);
                }
            }
        }

        $this->entityManager->persist($list);
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new VotingPlatformElectionVoteIsOpenEvent($election));
    }

    private function isValidCommitteeElection(CommitteeElection $committeeElection, Designation $designation): bool
    {
        $committee = $committeeElection->getCommittee();

        // validate voters
        if (!$this->entityManager->getRepository(CommitteeMembership::class)->committeeHasVotersForElection($committee, $designation)) {
            if ($this->io->isDebug()) {
                $this->io->warning(\sprintf('Committee "%s" does not have any voters', $committee->getSlug()));
            }

            return false;
        }

        // validate candidatures
        if (!$this->entityManager->getRepository(CommitteeCandidacy::class)->hasConfirmedCandidacies($committee, $designation)) {
            if ($this->io->isDebug()) {
                $this->io->warning(\sprintf('Committee "%s" does not have at least 1 candidate', $committee->getSlug()));
            }

            return false;
        }

        return true;
    }

    private function createNewElection(Designation $designation, ?ElectionEntity $electionEntity = null): Election
    {
        return new Election(
            $this->entityManager->getPartialReference(Designation::class, $designation->getId()),
            null,
            [new ElectionRound()],
            $electionEntity
        );
    }

    private function createVoterList(Election $election, array $adherents = []): VotersList
    {
        $list = new VotersList($election);

        foreach ($adherents as $adherent) {
            $list->addVoter($this->entityManager->getRepository(Voter::class)->findForAdherent($adherent) ?? new Voter($adherent));
        }

        return $list;
    }

    private function createCommitteeSupervisorCandidate(CommitteeCandidacy $candidacy): Candidate
    {
        $candidate = new Candidate(
            $candidacy->getFirstName(),
            $candidacy->getLastName(),
            $candidacy->getGender(),
            $candidacy->getAdherent()
        );

        $candidate->setImagePath($candidacy->getImagePath());
        $candidate->setBiography($candidacy->getBiography());
        $candidate->setFaithStatement($candidacy->getFaithStatement());
        $candidacy->take();

        return $candidate;
    }
}
