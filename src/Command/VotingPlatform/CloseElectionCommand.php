<?php

namespace App\Command\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Election\ResultCalculator;
use App\VotingPlatform\Notifier\Event\VotingPlatformElectionVoteIsOverEvent;
use App\VotingPlatform\Notifier\Event\VotingPlatformSecondRoundNotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CloseElectionCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:step-3:close-election';

    private SymfonyStyle $io;

    public function __construct(
        private readonly ElectionRepository $electionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ResultCalculator $resultManager,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Voting Platform: step 3: close election');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime();

        $this->io->progressStart();

        while ($elections = $this->electionRepository->getElectionsToCloseOrWithoutResults($date, 50)) {
            foreach ($elections as $election) {
                $this->doCloseElection($election);

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();
        }

        $this->io->progressFinish();

        return 0;
    }

    private function doCloseElection(Election $election): void
    {
        // 1. compute election result
        $electionResult = $this->resultManager->computeElectionResult($election);

        // 2. close election or start the second round
        if ($election->isOpen()) {
            if ($election->canClose()) {
                $election->close();

                $this->entityManager->flush();

                $this->eventDispatcher->dispatch(new VotingPlatformElectionVoteIsOverEvent($election));
            } else {
                $election->startSecondRound($electionResult->getNotElectedPools($election->getCurrentRound()));

                $this->entityManager->flush();

                $this->eventDispatcher->dispatch(new VotingPlatformSecondRoundNotificationEvent($election));
            }
        }

        // 3. persist results
        $this->entityManager->flush();
    }
}
