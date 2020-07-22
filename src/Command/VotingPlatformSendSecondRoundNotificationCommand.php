<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Voter;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Events;
use App\VotingPlatform\Notifier\Event\CommitteeElectionSecondRoundNotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VotingPlatformSendSecondRoundNotificationCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:send-second-round-notification';

    private $entityManager;
    private $dispatcher;
    private $electionRepository;
    private $voterRepository;

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        ElectionRepository $electionRepository,
        VoterRepository $voterRepository
    ) {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->electionRepository = $electionRepository;
        $this->voterRepository = $voterRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a reminder to vote on committee elections.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->progressStart();

        foreach ($this->electionRepository->getElectionsWithIncomingSecondRounds() as $election) {
            $this->sendElectionSecondRoundNotifications($election);

            $this->io->progressAdvance();

            $this->entityManager->detach($election);
            $this->entityManager->clear(Adherent::class);
            $this->entityManager->clear(Committee::class);
            $this->entityManager->clear(CommitteeElection::class);
            $this->entityManager->clear(ElectionRound::class);
            $this->entityManager->clear(Voter::class);
        }

        $this->io->progressFinish();
    }

    private function sendElectionSecondRoundNotifications(Election $election): void
    {
        foreach ($this->voterRepository->findVotersToNotifyForSecondRound($election) as $voter) {
            $this->sendSecondRoundNotification($election, $voter);
        }
    }

    private function sendSecondRoundNotification(Election $election, Voter $voter): void
    {
        $this->dispatcher->dispatch(Events::VOTE_SECOND_ROUND, new CommitteeElectionSecondRoundNotificationEvent(
            $voter->getAdherent(),
            $election->getDesignation(),
            $election->getElectionEntity()->getCommittee(),
            $election
        ));
    }
}
