<?php

declare(strict_types=1);

namespace App\Command\Adherent\Activity;

use App\Adherent\Activity\PopulateAdherentActivityCommand as PopulateCommand;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:adherent:populate-activity',
    description: 'Populate the aggregated adherent activity table from action history and hits',
)]
class PopulateAdherentActivityCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Delete all existing rows before populating');
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('reset')) {
            $this->connection->executeStatement('DELETE FROM adherent_activity');
            $io->note('Existing rows deleted.');
        }

        $this->bus->dispatch(new PopulateCommand());

        $io->success('Adherent activity population dispatched.');

        return self::SUCCESS;
    }
}
