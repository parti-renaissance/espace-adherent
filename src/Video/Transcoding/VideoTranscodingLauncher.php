<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Repository\VideoRepository;
use App\Video\Transcoding\Command\CheckVideoTranscodingStatusCommand;
use App\Video\Transcoding\Exception\TranscoderAtCapacityException;
use Doctrine\ORM\EntityManagerInterface;
use Google\ApiCore\ApiException;
use Google\ApiCore\ApiStatus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Shared orchestration for starting (or restarting) a transcoding job: creates the GCP job, marks the
 * Video as processing and schedules the first status poll. Used by both the ingest and the relaunch flows.
 */
class VideoTranscodingLauncher
{
    private const int INITIAL_POLL_DELAY_MS = 15000;

    // RPC statuses that mean the job request itself is invalid: retrying it as-is cannot succeed, so the
    // video is failed immediately. RESOURCE_EXHAUSTED is handled separately (capacity, see launch()).
    // Everything else (UNAVAILABLE, DEADLINE_EXCEEDED, INTERNAL, UNKNOWN, PERMISSION_DENIED — IAM can
    // propagate late after a deploy — etc.) is treated as transient and rethrown so Messenger retries
    // it within its bounded retry strategy.
    private const array PERMANENT_STATUSES = [
        ApiStatus::INVALID_ARGUMENT,
        ApiStatus::NOT_FOUND,
        ApiStatus::FAILED_PRECONDITION,
    ];

    public function __construct(
        private readonly VideoTranscoderInterface $transcoder,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly VideoRepository $videoRepository,
        private readonly string $videoOutputBucket,
        private readonly int $maxConcurrentTranscodingJobs,
    ) {
    }

    /**
     * @throws TranscoderAtCapacityException when the GCP concurrent-job quota is (about to be) reached,
     *                                       so the caller can defer the work instead of failing it
     */
    public function launch(Video $video, string $inputGcsUri): void
    {
        // Proactive gate: a cheap, conservative count of active jobs reduces pressure on the hard GCP
        // quota. It is best-effort (multi-worker races, manual jobs, cross-env): the reactive
        // RESOURCE_EXHAUSTED catch below is the actual guarantee. A threshold <= 0 disables the gate.
        if ($this->maxConcurrentTranscodingJobs > 0) {
            $activeJobs = $this->videoRepository->countActiveTranscodingJobs($video);

            if ($activeJobs >= $this->maxConcurrentTranscodingJobs) {
                throw TranscoderAtCapacityException::proactive($activeJobs, $this->maxConcurrentTranscodingJobs);
            }
        }

        $uuid = $video->getUuid()->toRfc4122();
        $outputUri = \sprintf('gs://%s/videos/%s/', $this->videoOutputBucket, $uuid);

        try {
            $jobName = $this->transcoder->createJob($inputGcsUri, $outputUri, $uuid, !$video->transcodeWithoutAudio);
        } catch (ApiException $exception) {
            // Quota overshoot the proactive gate let through (races, manual jobs): defer, do not fail.
            if (ApiStatus::RESOURCE_EXHAUSTED === $exception->getStatus()) {
                throw TranscoderAtCapacityException::reactive();
            }

            if (!\in_array($exception->getStatus(), self::PERMANENT_STATUSES, true)) {
                throw $exception; // transient: let Messenger retry the message
            }

            $video->status = VideoStatusEnum::FAILED;
            $video->failureReason = $exception->getBasicMessage();
            if (null !== $video->transcodingStartedAt) {
                $video->transcodingFinishedAt = new \DateTimeImmutable();
            }
            $this->entityManager->flush();

            return;
        }

        $video->transcodingJobName = $jobName;
        $video->status = VideoStatusEnum::PROCESSING;
        $video->transcodingStartedAt = new \DateTimeImmutable();
        $video->transcodingFinishedAt = null;
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
