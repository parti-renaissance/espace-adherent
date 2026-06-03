<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\SocialNetworkFeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Backfills author_name on feeds that predate the column, reading the value back from the raw payload
 * already stored on each row (raw_json.name). Idempotent and re-runnable: only rows whose author_name is
 * still null are scanned, and rows without a name in their payload are simply left untouched.
 *
 * Mutating a published feed re-triggers its Algolia/mirror indexation, so the new author_name reaches
 * already-published timeline records — treat a large run as a reindex operation (use --limit).
 */
#[AsCommand(
    name: 'app:social-network:backfill-feed-author-name',
    description: 'Fill author_name from the stored raw payload (raw_json.name) for feeds that predate the column.',
)]
class BackfillSocialNetworkFeedAuthorNameCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly SocialNetworkFeedRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Cap the number of feeds scanned (reindex/cost safety).')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Report how many feeds would be filled without writing anything.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $feeds = $this->repository->findWithMissingAuthorName($limit > 0 ? $limit : null);

        if (0 === \count($feeds)) {
            $this->io->success('No feed to backfill.');

            return self::SUCCESS;
        }

        $fillable = [];
        foreach ($feeds as $feed) {
            $name = $feed->rawJson['raw_json']['name'] ?? null;
            if (\is_string($name) && '' !== $name) {
                $fillable[] = [$feed, $name];
            }
        }

        $fillableCount = \count($fillable);
        $skipped = \count($feeds) - $fillableCount;

        if (0 === $fillableCount) {
            $this->io->success(\sprintf('No author name to backfill (%d feed(s) scanned, none carries raw_json.name).', \count($feeds)));

            return self::SUCCESS;
        }

        if ($input->getOption('dry-run')) {
            $this->io->note(\sprintf('%d feed(s) would be filled, %d skipped (no name in payload).', $fillableCount, $skipped));

            return self::SUCCESS;
        }

        if (!$this->io->confirm(\sprintf('Fill author_name for %d feed(s)?', $fillableCount), false)) {
            return self::FAILURE;
        }

        foreach ($fillable as [$feed, $name]) {
            $feed->authorName = $name;
        }
        $this->entityManager->flush();

        $this->io->success(\sprintf('%d author name(s) backfilled, %d skipped (no name in payload).', $fillableCount, $skipped));

        return self::SUCCESS;
    }
}
