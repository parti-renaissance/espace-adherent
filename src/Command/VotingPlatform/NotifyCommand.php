<?php

declare(strict_types=1);

namespace App\Command\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\CommitteeElectionRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Notifier\ElectionNotifier;
use App\VotingPlatform\Notifier\Event\CommitteeElectionCandidacyPeriodIsOverEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:voting-platform:notify',
    description: 'Voting Platform: send notification command',
)]
class NotifyCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DesignationRepository $designationRepository,
        private readonly CommitteeElectionRepository $committeeElectionRepository,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
        private readonly ElectionRepository $electionRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ElectionNotifier $electionNotifier,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime();

        $this->notifyForEndForCandidacy($date);
        $this->notifyBeforeVote($date);
        $this->notifyWithReminderToVote($date, Designation::NOTIFICATION_VOTE_REMINDER_1D);
        $this->notifyWithReminderToVote($date, Designation::NOTIFICATION_VOTE_REMINDER_1H);
        $this->notifyForForElectionResults($date);

        return self::SUCCESS;
    }

    private function notifyForEndForCandidacy(\DateTimeInterface $date): void
    {
        $designations = $this->designationRepository->getWithFinishCandidacyPeriod($date, [DesignationTypeEnum::COMMITTEE_ADHERENT]);

        foreach ($designations as $designation) {
            if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designation->getType()) {
                $this->notifyCommitteeElections($designation);
            }
        }
    }

    public function notifyCommitteeElections(Designation $designation): void
    {
        while ($committeeElections = $this->committeeElectionRepository->findAllToNotify($designation)) {
            foreach ($committeeElections as $committeeElection) {
                $memberships = $this->committeeMembershipRepository->findVotingMemberships($committee = $committeeElection->getCommittee());

                foreach ($memberships as $membership) {
                    $this->eventDispatcher->dispatch(new CommitteeElectionCandidacyPeriodIsOverEvent(
                        $membership->getAdherent(),
                        $designation,
                        $committee
                    ));
                }

                $committeeElection->setAdherentNotified(true);

                $this->entityManager->flush();
            }

            $this->entityManager->clear();
        }
    }

    private function notifyWithReminderToVote(\DateTimeInterface $date, int $notification): void
    {
        $elections = $this->electionRepository->getElectionsToClose($date, $notification);

        foreach ($elections as $election) {
            $this->electionNotifier->notifyVoteReminder($election, $notification);
        }
    }

    private function notifyBeforeVote(\DateTimeInterface $date): void
    {
        $elections = $this->electionRepository->findIncomingElections($date);

        foreach ($elections as $election) {
            $this->electionNotifier->notifyVoteAnnouncement($election);
        }
    }

    private function notifyForForElectionResults(\DateTimeInterface $date): void
    {
        $designations = $this->designationRepository->getWithActiveResultsPeriod($date);

        foreach ($designations as $designation) {
            foreach ($this->electionRepository->findAllForDesignation($designation) as $election) {
                $this->electionNotifier->notifyForForElectionResults($election);
            }
        }
    }
}
