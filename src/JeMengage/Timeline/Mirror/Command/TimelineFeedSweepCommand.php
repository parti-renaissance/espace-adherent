<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror\Command;

use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:timeline:sweep',
    description: 'Delete orphan timeline_feed rows not refreshed since the given threshold (run after a reindex drains).',
)]
class TimelineFeedSweepCommand extends Command
{
    public function __construct(private readonly TimelineFeedWriter $writer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('before', null, InputOption::VALUE_REQUIRED, 'Delete rows whose updated_at is strictly before this datetime (typically the reindex start time).')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip the confirmation prompt.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $before = trim((string) $input->getOption('before'));
        if ('' === $before) {
            $io->error('The --before option is required (e.g. --before="2026-06-05 18:00:00").');

            return Command::INVALID;
        }

        try {
            $threshold = new \DateTimeImmutable($before);
        } catch (\Exception) {
            $io->error(\sprintf('Invalid --before datetime: "%s".', $before));

            return Command::INVALID;
        }

        $count = $this->writer->countStaleBefore($threshold);

        if (0 === $count) {
            $io->success('No stale timeline rows to sweep.');

            return Command::SUCCESS;
        }

        if (
            !$input->getOption('force')
            && !$io->confirm(\sprintf('Delete %d timeline row(s) not refreshed since %s?', $count, $threshold->format('Y-m-d H:i:s')), false)
        ) {
            $io->warning('Aborted.');

            return Command::FAILURE;
        }

        $deleted = $this->writer->deleteStaleBefore($threshold);

        $io->success(\sprintf('Swept %d stale timeline row(s).', $deleted));

        return Command::SUCCESS;
    }
}
