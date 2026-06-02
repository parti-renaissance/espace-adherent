<?php

declare(strict_types=1);

namespace App\Video\Transcoding\Handler;

use App\Entity\VideoStatusEnum;
use App\Repository\VideoRepository;
use App\Video\Transcoding\Command\CheckVideoTranscodingStatusCommand;
use App\Video\Transcoding\VideoTranscoderInterface;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly MessageBusInterface $bus,
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
            $video->width ??= $status->width;
            $video->height ??= $status->height;
            $video->duration ??= $status->duration;
            $this->entityManager->flush();

            return;
        }

        if (VideoStatusEnum::FAILED === $status->state) {
            $video->status = VideoStatusEnum::FAILED;
            $video->failureReason = $status->error;
            $this->entityManager->flush();

            return;
        }

        // Still processing: fail on the wall-clock deadline, otherwise poll again later.
        if (time() > $command->startedAt + self::DEADLINE_SECONDS) {
            $video->status = VideoStatusEnum::FAILED;
            $video->failureReason = 'Transcoding timed out after 1h.';
            $this->entityManager->flush();

            return;
        }

        $this->bus->dispatch(
            new CheckVideoTranscodingStatusCommand($command->videoUuid, $command->jobName, $command->startedAt),
            [new DelayStamp(self::POLL_DELAY_MS)],
        );
    }
}
