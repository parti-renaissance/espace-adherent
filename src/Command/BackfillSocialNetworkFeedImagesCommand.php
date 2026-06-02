<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPhoto;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedImagesCommand;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedPhotoCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Backfills image publishing for feeds that already existed before the feature was deployed:
 * an image/avatar/photo source is set but its public path is still null.
 *
 * Re-dispatches the same idempotent messages the webhook fan-out uses, so it is safe to run
 * (and re-run): the deterministic public path plus skip-if-exists prevent duplicate uploads.
 * Only NULL public paths are targeted; a changed source is re-published by the nominal webhook.
 */
#[AsCommand(
    name: 'app:social-network:backfill-feed-images',
    description: 'Dispatch image publishing for existing feeds/photos whose public path is still null.',
)]
class BackfillSocialNetworkFeedImagesCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Cap the number of feeds and photos dispatched per type (quota/cost safety).')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List how many messages would be dispatched without dispatching.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $dryRun = (bool) $input->getOption('dry-run');

        $feedRows = $this->fetch(
            \sprintf('SELECT f.id AS id FROM %s f WHERE (f.imageUrl IS NOT NULL AND f.publicImagePath IS NULL) OR (f.avatarImageUrl IS NOT NULL AND f.publicAvatarImagePath IS NULL) ORDER BY f.id ASC', SocialNetworkFeed::class),
            $limit,
        );

        $photoRows = $this->fetch(
            \sprintf('SELECT p.id AS id, p.src AS src FROM %s p WHERE p.src IS NOT NULL AND p.publicSrc IS NULL ORDER BY p.id ASC', SocialNetworkFeedPhoto::class),
            $limit,
        );

        $total = \count($feedRows) + \count($photoRows);

        if (0 === $total) {
            $this->io->success('No feed image to backfill.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->io->note(\sprintf('%d feed(s) and %d photo(s) would be dispatched.', \count($feedRows), \count($photoRows)));

            return self::SUCCESS;
        }

        if (!$this->io->confirm(\sprintf('Dispatch image publishing for %d message(s)?', $total), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($total);

        foreach ($feedRows as $row) {
            $this->bus->dispatch(new PublishSocialNetworkFeedImagesCommand((int) $row['id']));
            $this->io->progressAdvance();
        }

        foreach ($photoRows as $row) {
            $this->bus->dispatch(new PublishSocialNetworkFeedPhotoCommand((int) $row['id'], (string) $row['src']));
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success(\sprintf('%d image publishing message(s) dispatched.', $total));

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetch(string $dql, int $limit): array
    {
        $query = $this->entityManager->createQuery($dql);

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        return $query->getArrayResult();
    }
}
