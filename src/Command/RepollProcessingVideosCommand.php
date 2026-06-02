<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Video\Transcoding\VideoTranscodingLauncher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Re-arms the status poll for videos stuck in PROCESSING.
 *
 * The 1h transcoding deadline is only enforced inside the poll handler, so if a poll message is lost
 * (RabbitMQ purge on deploy, worker down too long, crash between launch flush and dispatch) the video
 * stays PROCESSING forever. This re-dispatches a poll for its current job (no new GCP job is created).
 */
#[AsCommand(
    name: 'app:video:repoll-processing',
    description: 'Re-dispatch a status poll for videos stuck in PROCESSING (recover lost poll messages).',
)]
class RepollProcessingVideosCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VideoTranscodingLauncher $launcher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Cap the number of videos re-polled.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show how many videos would be re-polled without dispatching.')
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

        $queryBuilder = $this->entityManager
            ->getRepository(Video::class)
            ->createQueryBuilder('v')
            ->where('v.status = :status')
            ->andWhere('v.transcodingJobName IS NOT NULL')
            ->setParameter('status', VideoStatusEnum::PROCESSING)
            ->orderBy('v.id', 'ASC')
        ;

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        /** @var Video[] $videos */
        $videos = $queryBuilder->getQuery()->getResult();
        $total = \count($videos);

        if (0 === $total) {
            $this->io->success('No video stuck in PROCESSING.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->io->note(\sprintf('%d video(s) would be re-polled.', $total));

            return self::SUCCESS;
        }

        if (!$this->io->confirm(\sprintf('Re-poll %d video(s) stuck in PROCESSING?', $total), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($total);

        foreach ($videos as $video) {
            $this->launcher->scheduleStatusPoll($video);
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success(\sprintf('%d status poll(s) re-dispatched.', $total));

        return self::SUCCESS;
    }
}
