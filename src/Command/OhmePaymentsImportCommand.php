<?php

namespace App\Command;

use App\Ohme\ContactImporter;
use App\Ohme\PaymentImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ohme:import-payments',
    description: 'Import Ohme payments',
)]
class OhmePaymentsImportCommand extends Command
{
    private const MAX_PAGE_SIZE = 100;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly PaymentImporter $importer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

        $total = $this->importer->getPaymentsCount();

        if ($limit && $total > $limit) {
            $total = $limit;
        }

        if (0 === $total) {
            $this->io->text('No payment to import.');

            return self::SUCCESS;
        }

        $this->io->section('Importing payments');
        $this->io->progressStart($total);

        $pageSize = $limit ? min($limit, self::MAX_PAGE_SIZE) : self::MAX_PAGE_SIZE;
        $offset = 0;

        do {
            $currentPageSize = min($pageSize, $total - $offset);

            $this->importer->importPayments($currentPageSize, $offset);

            $this->io->progressAdvance($currentPageSize);

            $offset += $pageSize;
        } while ($offset < $total);

        $this->io->progressFinish();
        $this->io->success("$offset payments handled successfully.");

        return self::SUCCESS;
    }
}
