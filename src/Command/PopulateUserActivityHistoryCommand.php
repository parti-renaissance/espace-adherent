<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\Activity\Command\PopulateUserActivityHistoryCommand as PopulateCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:adherent:populate-activity-history',
    description: 'Populate the aggregated user activity history table from action history and hits',
)]
class PopulateUserActivityHistoryCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->bus->dispatch(new PopulateCommand());

        $io->success('User activity history population dispatched.');

        return self::SUCCESS;
    }
}
