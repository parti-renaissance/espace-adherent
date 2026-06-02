<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Video\Transcoding\Command\CheckVideoTranscodingStatusCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Shared orchestration for starting (or restarting) a transcoding job: creates the GCP job, marks the
 * Video as processing and schedules the first status poll. Used by both the ingest and the relaunch flows.
 */
class VideoTranscodingLauncher
{
    private const int INITIAL_POLL_DELAY_MS = 15000;

    public function __construct(
        private readonly VideoTranscoderInterface $transcoder,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly string $videoOutputBucket,
    ) {
    }

    public function launch(Video $video, string $inputGcsUri): void
    {
        $uuid = $video->getUuid()->toRfc4122();
        $outputUri = \sprintf('gs://%s/videos/%s/', $this->videoOutputBucket, $uuid);

        $jobName = $this->transcoder->createJob($inputGcsUri, $outputUri, $uuid);

        $video->transcodingJobName = $jobName;
        $video->status = VideoStatusEnum::PROCESSING;
        $this->entityManager->flush();

        $this->scheduleStatusPoll($video);
    }

    /**
     * Dispatches a status poll for the video's current job. Reused to re-arm the poll when a job is
     * already running but its initial poll dispatch may have been lost (crash between the launch flush
     * and the dispatch), so a stuck PROCESSING video recovers without creating a duplicate job.
     */
    public function scheduleStatusPoll(Video $video): void
    {
        $this->bus->dispatch(
            new CheckVideoTranscodingStatusCommand($video->getUuid()->toRfc4122(), (string) $video->transcodingJobName, time()),
            [new DelayStamp(self::INITIAL_POLL_DELAY_MS)],
        );
    }
}
