<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror\Command;

use App\JeMengage\Timeline\Mirror\Message\UpsertTimelineFeedCommand;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:timeline:reindex',
    description: 'Queue an async re-index of every current timeline item into the timeline_feed mirror.',
)]
class TimelineFeedReindexCommand extends Command
{
    use LockableTrait;

    private const BATCH_SIZE = 500;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Prevent overlapping runs from flooding the queue with duplicate upserts.
        if (!$this->lock()) {
            $io->warning('Another timeline reindex is already running.');

            return Command::FAILURE;
        }

        try {
            $startedAt = new \DateTimeImmutable();
            $total = 0;

            foreach ($this->rootClasses() as $rootClass) {
                $queued = $this->dispatchClass($rootClass);
                $total += $queued;
                $io->writeln(\sprintf('  %s: %d queued', $rootClass, $queued));
            }

            $io->success(\sprintf('Timeline reindex queued: %d upsert message(s) dispatched.', $total));
            $io->note(\sprintf(
                'Once the queue is drained, remove orphan rows with: app:timeline:sweep --before="%s"',
                $startedAt->format('Y-m-d H:i:s'),
            ));

            return Command::SUCCESS;
        } finally {
            $this->release();
        }
    }

    /**
     * Streams the identifiers of one root class by keyset pagination — only the scalar id is read,
     * never a hydrated entity — and dispatches one async upsert per item. The heavy normalization
     * runs later on the workers, so this command stays at flat, bounded memory.
     *
     * Descending by id (a monotonic auto-increment, so the most recently created rows first): on a
     * large backlog the freshest timeline items reach the workers — and the app — before the long tail.
     */
    private function dispatchClass(string $rootClass): int
    {
        $idField = $this->entityManager->getClassMetadata($rootClass)->getSingleIdentifierFieldName();
        $queued = 0;
        $lastId = null;

        do {
            $qb = $this->entityManager->getRepository($rootClass)->createQueryBuilder('e')
                ->select(\sprintf('e.%s AS id', $idField))
                ->orderBy('e.'.$idField, 'DESC')
                ->setMaxResults(self::BATCH_SIZE)
            ;

            if (null !== $lastId) {
                $qb->where('e.'.$idField.' < :lastId')->setParameter('lastId', $lastId);
            }

            $rows = $qb->getQuery()->getScalarResult();

            foreach ($rows as $row) {
                $lastId = $row['id'];
                $this->bus->dispatch(new UpsertTimelineFeedCommand($rootClass, $lastId));
                ++$queued;
            }
        } while (self::BATCH_SIZE === \count($rows));

        return $queued;
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
