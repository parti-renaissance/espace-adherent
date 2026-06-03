<?php

declare(strict_types=1);

namespace App\Video\Transcoding\Handler;

use App\Entity\VideoStatusEnum;
use App\Repository\VideoRepository;
use App\Video\Transcoding\Command\CheckVideoTranscodingStatusCommand;
use App\Video\Transcoding\Exception\TranscoderAtCapacityException;
use App\Video\Transcoding\TranscodedVideoProbeInterface;
use App\Video\Transcoding\VideoTranscoderInterface;
use App\Video\Transcoding\VideoTranscodingLauncher;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class CheckVideoTranscodingStatusCommandHandler
{
    private const int POLL_DELAY_MS = 30000;
    private const int DEADLINE_SECONDS = 3600;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VideoRepository $videoRepository,
        private readonly VideoTranscoderInterface $transcoder,
        private readonly TranscodedVideoProbeInterface $probe,
        private readonly MessageBusInterface $bus,
        private readonly VideoTranscodingLauncher $launcher,
        private readonly LoggerInterface $logger,
        private readonly string $gcloudBucket,
    ) {
    }

    public function __invoke(CheckVideoTranscodingStatusCommand $command): void
    {
        $video = $this->videoRepository->findOneByUuid($command->videoUuid);

        if (null === $video || VideoStatusEnum::PROCESSING !== $video->status) {
            return;
        }

        // A relaunch started a new job: this poll targets an outdated one, drop it.
        if ($video->transcodingJobName !== $command->jobName) {
            return;
        }

        $status = $this->transcoder->getJob($command->jobName);

        if (VideoStatusEnum::READY === $status->state) {
            $video->status = VideoStatusEnum::READY;
            $video->mediaPath = 'videos/'.$command->videoUuid;
            $video->transcodingFinishedAt = new \DateTimeImmutable();

            $mediaInfo = $this->probe->probe($video);
            $video->width ??= $mediaInfo->width;
            $video->height ??= $mediaInfo->height;
            $video->duration ??= $mediaInfo->duration;
            $this->entityManager->flush();

            return;
        }

        if (VideoStatusEnum::FAILED === $status->state) {
            // Source with no audio track: retry once without audio. The flip is atomic (the poll message
            // has no lock) so concurrent FAILED polls don't spawn two no-audio jobs — only the winner
            // relaunches; losers drop. originalPath is the archived source the relaunch reads.
            if ($this->isMissingAudioTrack($status->error)
                && !$video->transcodeWithoutAudio
                && null !== $video->originalPath
            ) {
                if ($this->videoRepository->flagTranscodeWithoutAudio($video)) {
                    $video->transcodeWithoutAudio = true; // keep the managed entity consistent for launch()

                    try {
                        $this->launcher->launch($video, \sprintf('gs://%s/%s', $this->gcloudBucket, $video->originalPath));
                    } catch (TranscoderAtCapacityException $exception) {
                        // Mid-poll reactive retry: a deferred relaunch would be blocked by its own
                        // "if PROCESSING return" guard (the video is still PROCESSING here), so fail it
                        // instead. Recoverable via `app:video:retranscode --status=failed`, which resets
                        // the no-audio flag and relaunches once capacity frees.
                        $video->status = VideoStatusEnum::FAILED;
                        $video->failureReason = 'Transcoder at capacity during no-audio retry.';
                        $video->transcodingFinishedAt = new \DateTimeImmutable();
                        $this->entityManager->flush();

                        $this->logger->error('[Video transcode] gave up no-audio retry: transcoder at capacity.', [
                            'uuid' => $video->getUuid()->toRfc4122(),
                            'cause' => $exception->cause,
                            'active_jobs' => $exception->activeJobCount,
                        ]);
                    }
                }

                return;
            }

            $video->status = VideoStatusEnum::FAILED;
            $video->failureReason = $status->error;
            $video->transcodingFinishedAt = new \DateTimeImmutable();
            $this->entityManager->flush();

            return;
        }

        // Still processing: fail on the wall-clock deadline, otherwise poll again later.
        if (time() > $command->startedAt + self::DEADLINE_SECONDS) {
            $video->status = VideoStatusEnum::FAILED;
            $video->failureReason = 'Transcoding timed out after 1h.';
            $video->transcodingFinishedAt = new \DateTimeImmutable();
            $this->entityManager->flush();

            return;
        }

        $this->bus->dispatch(
            new CheckVideoTranscodingStatusCommand($command->videoUuid, $command->jobName, $command->startedAt),
            [new DelayStamp(self::POLL_DELAY_MS)],
        );
    }

    /**
     * The Transcoder fails a job whose source has no audio track with a message such as
     * "atom atom0 does not have any inputs ([input0]) with an audio track". Substring match is
     * best-effort: a wording change degrades to a normal FAILED, never worse.
     */
    private function isMissingAudioTrack(?string $message): bool
    {
        return null !== $message && str_contains($message, 'audio track');
    }
}
