<?php

namespace App\Command\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Vote;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\ElectionStatusEnum;
use App\VotingPlatform\Notifier\Event\VotingPlatformVoteReminderEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:voting-platform:send-vote-reminder',
    description: 'Send a reminder to vote on committee elections.',
)]
class SendVoteReminderCommand extends Command
{
    private $entityManager;
    private $dispatcher;
    private $electionRepository;
    private $voterRepository;

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    /**
     * @var CommitteeMembershipRepository
     */
    private $committeeMembershipRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        ElectionRepository $electionRepository,
        VoterRepository $voterRepository,
        CommitteeMembershipRepository $committeeMembershipRepository
    ) {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->electionRepository = $electionRepository;
        $this->voterRepository = $voterRepository;
        $this->committeeMembershipRepository = $committeeMembershipRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('date', null, InputOption::VALUE_REQUIRED, 'date interval, it is used in query to find the election to close (ex: use `--date=3` for 3 days)')
            ->addOption('designation-id', null, InputOption::VALUE_REQUIRED, 'Designation id')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($designationId = (int) $input->getOption('designation-id')) {
            $elections = $this->electionRepository->findBy(['designation' => $designationId, 'status' => ElectionStatusEnum::OPEN]);
        } else {
            $days = (int) $input->getOption('date');

            if ($days <= 0) {
                throw new \InvalidArgumentException('date argument is invalid');
            }
            $voteEndDate = new \DateTime(\sprintf('+%d days', $days));
            $elections = $this->electionRepository->getElectionsToClose($voteEndDate);
        }

        $this->io->progressStart();

        foreach ($elections as $election) {
            if (
                !$election->getDesignation()->isNotificationVoteReminderEnabled()
                || !$election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_REMINDER)
            ) {
                continue;
            }

            $this->sendElectionVoteReminders($election);

            $this->io->progressAdvance();
            $this->entityManager->clear();
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function sendElectionVoteReminders(Election $election): void
    {
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            /** @var VoteRepository $voteRepository */
            $voteRepository = $this->entityManager->getRepository(Vote::class);
            // need to transform a Proxy class to a Committee one
            $committeeName = $election->getElectionEntity()->getCommittee()->getName();

            foreach ($this->committeeMembershipRepository->findVotingForSupervisorMembershipsToNotify($election->getElectionEntity()->getCommittee(), $election->getDesignation()) as $committeeMembership) {
                $adherent = $committeeMembership->getAdherent();

                if ($voteRepository->findVoteForDesignation($adherent, $election->getDesignation())) {
                    continue;
                }

                $this->dispatcher->dispatch(new VotingPlatformVoteReminderEvent($election, $adherent));

                $adherent->notifyForElection();
            }
        } else {
            foreach ($this->voterRepository->findVotersToRemindForElection($election) as $voter) {
                $this->dispatcher->dispatch(new VotingPlatformVoteReminderEvent($election, $voter->getAdherent()));
            }
        }

        $election->markSentNotification(Designation::NOTIFICATION_VOTE_REMINDER);
        $this->entityManager->flush();
    }
}
