<?php

declare(strict_types=1);

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
    name: 'app:ohme:import',
    description: 'Import Ohme contacts and payments',
)]
class OhmeImportCommand extends Command
{
    private const MAX_PAGE_SIZE = 100;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly ContactImporter $contactImporter,
        private readonly PaymentImporter $paymentImporter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('contact-ohme-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('contact-email', null, InputOption::VALUE_REQUIRED)
            ->addOption('contact-uuid-adherent', null, InputOption::VALUE_REQUIRED)
            ->addOption('with-payments', null, InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importContacts(
            (int) $input->getOption('limit'),
            $input->getOption('contact-ohme-id'),
            $input->getOption('contact-email'),
            $input->getOption('contact-uuid-adherent')
        );

        if ($input->getOption('with-payments')) {
            $this->importPayments();
        }

        return self::SUCCESS;
    }

    private function importContacts(
        ?int $limit = null,
        ?string $ohmeId = null,
        ?string $email = null,
        ?string $uuidAdherent = null,
    ): void {
        $filters = array_filter([
            'ohme_id' => $ohmeId,
            'email' => $email,
            'uuid_adherent' => $uuidAdherent,
        ]);

        $total = $this->contactImporter->getContactsCount($filters);

        if ($limit && $total > $limit) {
            $total = $limit;
        }

        if (0 === $total) {
            $this->io->text('No contact to import.');

            return;
        }

        $this->io->section('Importing contacts');
        $this->io->progressStart($total);

        $pageSize = $limit ? min($limit, self::MAX_PAGE_SIZE) : self::MAX_PAGE_SIZE;
        $offset = 0;

        do {
            $contactsCount = $this->contactImporter->importContacts($pageSize, $offset, $filters);

            $this->io->progressAdvance($contactsCount);

            $offset += $contactsCount;
        } while ($offset < $total);

        $this->io->progressFinish();
        $this->io->success("$offset contacts handled successfully.");
    }

    private function importPayments(): void
    {
        $total = $this->paymentImporter->getPaymentsCount();

        if (0 === $total) {
            $this->io->text('No payment to import.');

            return;
        }

        $this->io->section('Importing payments');
        $this->io->progressStart($total);

        $pageSize = $total ? min($total, self::MAX_PAGE_SIZE) : self::MAX_PAGE_SIZE;
        $offset = 0;

        do {
            $paymentsCount = $this->paymentImporter->importPayments($pageSize, $offset);

            $this->io->progressAdvance($paymentsCount);

            $offset += $pageSize;
        } while ($offset < $total);

        $this->io->progressFinish();
        $this->io->success("$offset payments handled successfully.");
    }
}
