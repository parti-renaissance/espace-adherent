<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Procuration\V2\Command\InitialRequestReminderCommand;
use App\Repository\Procuration\ProcurationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, '', 500)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = $input->getOption('batch-size');

        $this->io->title('Procuration initial requests reminder');
        $this->io->text('Fetching initial requests to remind');

        $paginator = $this->getQueryBuilder();
        $total = $paginator->count();

        $this->io->text(\sprintf('Found %s initial requests to remind', $total));

        $paginator->getQuery()->setMaxResults($batchSize);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $initialRequest) {
                $this->bus->dispatch(new InitialRequestReminderCommand($initialRequest->getUuid()));

                $this->io->progressAdvance();
                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $total);

        $this->io->progressFinish();

        $this->io->success('Done.');

        return self::SUCCESS;
    }

    /**
     * @return Paginator|ProcurationRequest[]
     */
    private function getQueryBuilder(): Paginator
    {
        return new Paginator(
            $this
                ->procurationRequestRepository
                ->createQueryBuilder('pr')
                ->select('PARTIAL pr.{id, uuid}')
                ->where('pr.remindedAt IS NULL')
                ->getQuery()
        );
    }
}
