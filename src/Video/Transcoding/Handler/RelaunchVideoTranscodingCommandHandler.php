<?php

declare(strict_types=1);

namespace App\Video\Transcoding\Handler;

use App\Entity\VideoStatusEnum;
use App\Repository\VideoRepository;
use App\Video\Transcoding\Command\RelaunchVideoTranscodingCommand;
use App\Video\Transcoding\Exception\TranscoderAtCapacityException;
use App\Video\Transcoding\TranscoderCapacityDeferral;
use App\Video\Transcoding\VideoTranscodingLauncher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RelaunchVideoTranscodingCommandHandler
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly VideoTranscodingLauncher $launcher,
        private readonly TranscoderCapacityDeferral $capacityDeferral,
        private readonly LoggerInterface $logger,
        private readonly string $gcloudBucket,
    ) {
    }

    public function __invoke(RelaunchVideoTranscodingCommand $command): void
    {
        $video = $this->videoRepository->findOneByUuid($command->videoUuid);

        if (null === $video || null === $video->originalPath) {
            $this->logger->warning('[Video relaunch] missing video or originalPath.', ['uuid' => $command->videoUuid]);

            return;
        }

        if (VideoStatusEnum::PROCESSING === $video->status) {
            return;
        }

        $video->failureReason = null;

        try {
            $this->launcher->launch($video, \sprintf('gs://%s/%s', $this->gcloudBucket, $video->originalPath));
        } catch (TranscoderAtCapacityException $exception) {
            $this->capacityDeferral->deferOrFail($command, $video, $exception);
        }
    }
}
