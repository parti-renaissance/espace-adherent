<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Video\Transcoding\Command\RelaunchVideoTranscodingCommand;
use App\Video\Transcoding\TranscoderCapacityDeferral;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Re-runs transcoding from the durable archived source (Video.originalPath) for a set of videos.
 *
 * Use cases, selected by --status:
 *  - failed:  replay videos that failed transcoding (e.g. after fixing IAM or job config, or a capacity give-up);
 *  - ready:   re-transcode already published videos after a TranscodingJobConfigFactory change;
 *  - pending: recover orphans left PENDING when a capacity-deferral message was lost (crash) or
 *             dead-lettered. Requires --older-than >= the deferral horizon so a still-live deferral
 *             chain (different lock key than relaunch) is never raced into a duplicate job.
 *
 * The job writes to the same gs://OUTPUT_BUCKET/videos/<uuid>/ prefix, overwriting the previous output.
 * Only videos with a non-null originalPath are eligible (others are not part of this pipeline and the
 * relaunch handler would skip them anyway).
 *
 * Caveat: a re-transcoded video goes back to PROCESSING and leaves the timeline (API serves READY only)
 * until the job completes. Use --limit to re-transcode in batches.
 */
#[AsCommand(
    name: 'app:video:retranscode',
    description: 'Re-run transcoding from the archived source for failed videos, or for ready videos after a config change.',
)]
class RetranscodeVideosCommand extends Command
{
    private const array STATUS_MAP = [
        'failed' => VideoStatusEnum::FAILED,
        'ready' => VideoStatusEnum::READY,
        'pending' => VideoStatusEnum::PENDING,
    ];

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
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Which videos to re-transcode: '.implode('|', array_keys(self::STATUS_MAP)), 'failed')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Cap the number of videos re-transcoded (quota/cost and timeline-impact safety).')
            ->addOption('older-than', null, InputOption::VALUE_REQUIRED, 'Only re-transcode videos not updated in the last N minutes (0 = no age filter). Mandatory and >= the deferral horizon for --status=pending.', '0')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show how many videos would be re-transcoded without dispatching.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statusOption = (string) $input->getOption('status');

        if (!isset(self::STATUS_MAP[$statusOption])) {
            throw new \InvalidArgumentException(\sprintf('Invalid --status "%s", expected one of: %s.', $statusOption, implode(', ', array_keys(self::STATUS_MAP))));
        }

        $limit = (int) $input->getOption('limit');
        $olderThanMinutes = (int) $input->getOption('older-than');
        $dryRun = (bool) $input->getOption('dry-run');

        // PENDING orphans share no lock key with a still-live ingest deferral chain, so only recover those
        // older than the deferral horizon — by then any live chain has ended (success or FAILED give-up).
        if ('pending' === $statusOption && $olderThanMinutes < TranscoderCapacityDeferral::DEFERRAL_HORIZON_MINUTES) {
            throw new \InvalidArgumentException(\sprintf('--status=pending requires --older-than >= %d (the deferral horizon) to avoid racing in-flight transcoding messages.', TranscoderCapacityDeferral::DEFERRAL_HORIZON_MINUTES));
        }

        $queryBuilder = $this->entityManager
            ->getRepository(Video::class)
            ->createQueryBuilder('v')
            ->where('v.status = :status')
            ->andWhere('v.originalPath IS NOT NULL')
            ->setParameter('status', self::STATUS_MAP[$statusOption])
            ->orderBy('v.id', 'ASC')
        ;

        if ($olderThanMinutes > 0) {
            $queryBuilder
                ->andWhere('v.updatedAt < :threshold')
                ->setParameter('threshold', new \DateTimeImmutable(\sprintf('-%d minutes', $olderThanMinutes)))
            ;
        }

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        /** @var Video[] $videos */
        $videos = $queryBuilder->getQuery()->getResult();
        $total = \count($videos);

        if (0 === $total) {
            $this->io->success(\sprintf('No "%s" video with an archived source to re-transcode.', $statusOption));

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->io->note(\sprintf('%d "%s" video(s) would be re-transcoded.', $total, $statusOption));

            return self::SUCCESS;
        }

        if (!$this->io->confirm(\sprintf('Re-transcode %d "%s" video(s)? They will leave the timeline until done.', $total, $statusOption), false)) {
            return self::FAILURE;
        }

        // Clear the no-audio flag so a config-change re-transcode (--status=ready) regenerates with audio;
        // the reactive retry will drop audio again only if the source is genuinely silent. Flushed before
        // dispatch so the relaunch (routed sync in tests) reads the reset value.
        foreach ($videos as $video) {
            $video->transcodeWithoutAudio = false;
        }
        $this->entityManager->flush();

        $this->io->progressStart($total);

        foreach ($videos as $video) {
            $this->bus->dispatch(new RelaunchVideoTranscodingCommand($video->getUuid()->toRfc4122()));
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success(\sprintf('%d re-transcoding message(s) dispatched.', $total));

        return self::SUCCESS;
    }
}
