<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\SocialNetwork\Video\Command\TranscodeSocialNetworkVideoCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Backfills the transcoding pipeline for social network videos that already existed
 * before the transcoding feature was deployed: a stream URL is set but no Video is linked yet.
 *
 * Re-dispatches the same idempotent message the webhook fan-out uses, so it is safe to run
 * (and re-run): find-or-create by Video.sourceUri (UNIQUE) plus the message lock prevent duplicates.
 */
#[AsCommand(
    name: 'app:social-network:backfill-video-transcoding',
    description: 'Dispatch transcoding for existing feed videos that have a stream URL but no linked Video.',
)]
class BackfillSocialNetworkVideoTranscodingCommand extends Command
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
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Cap the number of videos dispatched (quota/cost safety).')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List how many videos would be dispatched without dispatching.')
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

        $query = $this->entityManager
            ->createQuery(
                \sprintf(
                    'SELECT v.id AS id, v.streamUrl AS streamUrl FROM %s v WHERE v.streamUrl IS NOT NULL AND v.video IS NULL ORDER BY v.id ASC',
                    SocialNetworkFeedVideo::class
                )
            )
        ;

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        $rows = $query->getArrayResult();
        $total = \count($rows);

        if (0 === $total) {
            $this->io->success('No feed video to backfill.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->io->note(\sprintf('%d feed video(s) would be dispatched.', $total));

            return self::SUCCESS;
        }

        if (!$this->io->confirm(\sprintf('Dispatch transcoding for %d feed video(s)?', $total), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($total);

        foreach ($rows as $row) {
            $this->bus->dispatch(new TranscodeSocialNetworkVideoCommand((int) $row['id'], (string) $row['streamUrl']));
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success(\sprintf('%d transcoding message(s) dispatched.', $total));

        return self::SUCCESS;
    }
}
