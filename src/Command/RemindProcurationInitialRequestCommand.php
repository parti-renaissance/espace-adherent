<?php

namespace App\Command;

use App\Procuration\V2\Command\InitialRequestReminderCommand;
use App\Repository\Procuration\ProcurationRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:procuration:remind-initial-request',
    description: 'This command finds initial requests and sends an email reminder',
)]
class RemindProcurationInitialRequestCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly ProcurationRequestRepository $procurationRequestRepository,
        private readonly MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Procuration initial requests reminder');
        $this->io->text('Fetching initial requests to remind');

        $initialRequests = $this->procurationRequestRepository->findAllToRemind();

        $this->io->text(sprintf('Found %s initial requests to remind', $total = \count($initialRequests)));

        $this->io->progressStart($total);

        foreach ($initialRequests as $initialRequest) {
            $this->bus->dispatch(new InitialRequestReminderCommand($initialRequest->getUuid()));

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Done.');

        return self::SUCCESS;
    }
}
