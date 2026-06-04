<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror\Command;

use App\JeMengage\Timeline\Mirror\TimelineFeedResolver;
use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:timeline:reindex',
    description: 'Rebuild the whole timeline_feed mirror (upsert all current items, sweep stale rows).',
)]
class TimelineFeedReindexCommand extends Command
{
    use LockableTrait;

    private const BATCH_SIZE = 500;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TimelineFeedResolver $resolver,
        private readonly TimelineFeedWriter $writer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Prevent concurrent rebuilds: two runs would sweep each other's freshly-written rows.
        if (!$this->lock()) {
            $io->warning('Another timeline reindex is already running.');

            return Command::FAILURE;
        }

        try {
            return $this->rebuild($io);
        } finally {
            $this->release();
        }
    }

    /**
     * Upsert every current timeline item, then sweep the rows left untouched (items that no longer
     * exist). On any per-item failure the sweep is skipped, so a transient error never deletes a
     * still-valid row.
     */
    private function rebuild(SymfonyStyle $io): int
    {
        $runStartedAt = new \DateTimeImmutable();
        $buffer = [];
        $upserted = 0;
        $failures = 0;

        foreach ($this->rootClasses() as $rootClass) {
            $query = $this->entityManager->getRepository($rootClass)->createQueryBuilder('e')->getQuery();

            foreach ($query->toIterable() as $entity) {
                try {
                    $document = $this->resolver->resolve($entity);
                } catch (\Throwable $e) {
                    ++$failures;
                    $io->warning(\sprintf('Failed to resolve %s: %s', $entity::class, $e->getMessage()));

                    continue;
                }

                if (null === $document || $document->isRemoval()) {
                    continue;
                }

                $buffer[] = $document;
                ++$upserted;

                if (\count($buffer) >= self::BATCH_SIZE) {
                    $this->writer->bulkUpsert($buffer);
                    $buffer = [];
                    $this->entityManager->clear();
                }
            }
        }

        if ([] !== $buffer) {
            $this->writer->bulkUpsert($buffer);
        }

        if ($failures > 0) {
            $io->error(\sprintf('%d item(s) failed to resolve; %d upserted, stale rows left untouched.', $failures, $upserted));

            return Command::FAILURE;
        }

        $deleted = $this->writer->deleteStaleBefore($runStartedAt);

        $io->success(\sprintf('Timeline mirror rebuilt (%d upserted, %d stale rows swept).', $upserted, $deleted));

        return Command::SUCCESS;
    }

    /**
     * Distinct ORM root classes to iterate, deduplicated by root entity name so a
     * single-table-inheritance root (e.g. Survey) is iterated once and covers its subtypes.
     *
     * @return list<class-string>
     */
    private function rootClasses(): array
    {
        $roots = [];
        foreach (array_keys(TimelineFeedTypeEnum::CLASS_MAPPING) as $class) {
            $root = $this->entityManager->getClassMetadata($class)->rootEntityName;
            $roots[$root] = $root;
        }

        return array_values($roots);
    }
}
