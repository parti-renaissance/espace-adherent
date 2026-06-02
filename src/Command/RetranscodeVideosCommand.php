<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Video\Transcoding\Command\RelaunchVideoTranscodingCommand;
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
 * Two use cases, selected by --status:
 *  - failed: replay videos that failed transcoding (e.g. after fixing IAM or job config);
 *  - ready:  re-transcode already published videos after a TranscodingJobConfigFactory change.
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
        $dryRun = (bool) $input->getOption('dry-run');

        $queryBuilder = $this->entityManager
            ->getRepository(Video::class)
            ->createQueryBuilder('v')
            ->where('v.status = :status')
            ->andWhere('v.originalPath IS NOT NULL')
            ->setParameter('status', self::STATUS_MAP[$statusOption])
            ->orderBy('v.id', 'ASC')
        ;

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
