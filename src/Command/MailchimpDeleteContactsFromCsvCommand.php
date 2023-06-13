<?php

namespace App\Command;

use App\Mailchimp\Synchronisation\Command\AdherentDeleteCommand;
use App\Mailchimp\Synchronisation\Command\RemoveApplicationRequestCandidateCommand;
use App\Mailchimp\Synchronisation\Command\RemoveNewsletterMemberCommand;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'mailchimp:delete:contact',
)]
class MailchimpDeleteContactsFromCsvCommand extends Command
{
    private $bus;
    /** @var SymfonyStyle */
    private $io;
    private $storage;

    public function __construct(MessageBusInterface $bus, FilesystemInterface $storage)
    {
        $this->bus = $bus;
        $this->storage = $storage;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'File csv with contact mails')
            ->addOption('adherents', null, InputOption::VALUE_NONE)
            ->addOption('candidates', null, InputOption::VALUE_NONE)
            ->addOption('newsletters', null, InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $adherents = $input->getOption('adherents');
        $candidates = $input->getOption('candidates');
        $newsletters = $input->getOption('newsletters');

        if (!($adherents || $candidates || $newsletters)) {
            throw new InvalidArgumentException('You should specify the type of contact');
        }

        $reader = Reader::createFromStream($this->storage->readStream($input->getArgument('file')));

        if (false === $this->io->confirm(sprintf('Are you sure to remove %d contacts?', $count = $reader->count()), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($count);

        foreach ($reader as $row) {
            $command = null;
            $this->io->progressAdvance();

            $mail = current($row);
            if ($adherents) {
                $command = new AdherentDeleteCommand($mail);
            } elseif ($candidates) {
                $command = new RemoveApplicationRequestCandidateCommand($mail);
            } elseif ($newsletters) {
                $command = new RemoveNewsletterMemberCommand($mail);
            }

            $this->bus->dispatch($command);
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }
}
