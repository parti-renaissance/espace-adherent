<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer\Command;

use App\Entity\Timeline\TimelineFeed;
use App\JeMengage\Timeline\Indexer\IndexerClient;
use App\JeMengage\Timeline\Indexer\IndexerKind;
use App\JeMengage\Timeline\Indexer\IndexerPayloadFactory;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:timeline:indexer:push',
    description: 'Push all pushable timeline_feed rows to the external indexer (batch). Backfill / convergence.',
)]
class TimelineFeedPushCommand extends Command
{
    use LockableTrait;

    private const BATCH_SIZE = 500;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IndexerPayloadFactory $payloadFactory,
        private readonly IndexerClient $client,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Prevent concurrent pushes from racing on the same rows.
        if (!$this->lock()) {
            $io->warning('Another timeline indexer push is already running.');

            return Command::FAILURE;
        }

        try {
            return $this->push($io);
        } finally {
            $this->release();
        }
    }

    private function push(SymfonyStyle $io): int
    {
        $query = $this->entityManager->getRepository(TimelineFeed::class)
            ->createQueryBuilder('t')
            ->where('t.type IN (:types)')
            ->setParameter('types', $this->pushableTypes())
            ->getQuery();

        $buffer = [];
        $pushed = 0;
        $skipped = 0;

        foreach ($query->toIterable() as $row) {
            $payload = $this->payloadFactory->create($row);

            // Pushable type but inexpressible audience (leak guard): not representable, skip.
            if (null === $payload) {
                ++$skipped;

                continue;
            }

            $buffer[] = $payload;
            ++$pushed;

            if (\count($buffer) >= self::BATCH_SIZE) {
                $this->client->indexBatch($buffer);
                $buffer = [];
                $this->entityManager->clear();
            }
        }

        if ([] !== $buffer) {
            $this->client->indexBatch($buffer);
        }

        $io->success(\sprintf('Timeline indexer push done: %d pushed, %d skipped (not representable).', $pushed, $skipped));

        return Command::SUCCESS;
    }

    /**
     * Internal types pushable to the indexer, derived from the single mapping source (IndexerKind).
     *
     * @return list<string>
     */
    private function pushableTypes(): array
    {
        return array_values(array_filter(
            array_unique(array_values(TimelineFeedTypeEnum::CLASS_MAPPING)),
            static fn (string $type): bool => null !== IndexerKind::fromInternalType($type),
        ));
    }
}
