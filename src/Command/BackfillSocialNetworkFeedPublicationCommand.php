<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Repository\SocialNetworkFeedRepository;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedImagesCommand;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedPhotoCommand;
use App\SocialNetwork\Publication\Command\CheckSocialNetworkFeedPublicationCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Backfills publication for feeds that predate the feature (published = false): resets their public
 * media paths so the copy handlers re-publish the bytes to the media bucket, then re-arms the
 * publication poller. Idempotent and re-runnable (publish() skips bytes already present on the CDN).
 */
#[AsCommand(
    name: 'app:social-network:backfill-feed-publication',
    description: 'Reset media paths and re-arm the publication poller for unpublished feeds.',
)]
class BackfillSocialNetworkFeedPublicationCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly SocialNetworkFeedRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Cap the number of feeds reprocessed (quota/cost safety).')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show how many feeds would be reprocessed without touching anything.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $feeds = $this->repository->findUnpublished($limit > 0 ? $limit : null);
        $count = \count($feeds);

        if (0 === $count) {
            $this->io->success('No unpublished feed to backfill.');

            return self::SUCCESS;
        }

        if ($input->getOption('dry-run')) {
            $this->io->note(\sprintf('%d feed(s) would be reset and reprocessed.', $count));

            return self::SUCCESS;
        }

        if (!$this->io->confirm(\sprintf('Reset media paths and reprocess %d feed(s)?', $count), false)) {
            return self::FAILURE;
        }

        foreach ($feeds as $feed) {
            $this->resetMediaPaths($feed);
        }
        $this->entityManager->flush();

        $this->io->progressStart($count);
        foreach ($feeds as $feed) {
            $this->dispatchReprocessing($feed);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        $this->io->success(\sprintf('%d feed(s) reset and queued for re-publication.', $count));

        return self::SUCCESS;
    }

    private function resetMediaPaths(SocialNetworkFeed $feed): void
    {
        // The copy handlers skip when the public path already equals the source's expected path.
        // Reset to null so the bytes are re-copied to the media bucket (they live in the old bucket).
        $feed->publicImagePath = null;
        $feed->publicAvatarImagePath = null;

        foreach ($feed->photos as $photo) {
            $photo->publicSrc = null;
        }
    }

    private function dispatchReprocessing(SocialNetworkFeed $feed): void
    {
        if (null !== $feed->imageUrl || null !== $feed->avatarImageUrl) {
            $this->bus->dispatch(new PublishSocialNetworkFeedImagesCommand($feed->getId()));
        }

        foreach ($feed->photos as $photo) {
            if (null === $photo->src) {
                continue;
            }

            $this->bus->dispatch(new PublishSocialNetworkFeedPhotoCommand($photo->id, $photo->src));
        }

        $this->bus->dispatch(new CheckSocialNetworkFeedPublicationCommand($feed->getId(), time()));
    }
}
