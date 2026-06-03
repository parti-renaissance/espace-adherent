<?php

declare(strict_types=1);

namespace App\SocialNetwork\Video\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Repository\SocialNetworkFeedVideoRepository;
use App\SocialNetwork\Video\Command\TranscodeSocialNetworkVideoCommand;
use App\Video\Storage\VideoSourceArchiverInterface;
use App\Video\Transcoding\Exception\TranscoderAtCapacityException;
use App\Video\Transcoding\TranscoderCapacityDeferral;
use App\Video\Transcoding\VideoTranscodingLauncher;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TranscodeSocialNetworkVideoCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SocialNetworkFeedVideoRepository $socialNetworkFeedVideoRepository,
        private readonly VideoSourceArchiverInterface $archiver,
        private readonly VideoTranscodingLauncher $launcher,
        private readonly TranscoderCapacityDeferral $capacityDeferral,
        private readonly LoggerInterface $logger,
        private readonly string $gcloudBucket,
    ) {
    }

    public function __invoke(TranscodeSocialNetworkVideoCommand $command): void
    {
        $feedVideo = $this->socialNetworkFeedVideoRepository->find($command->socialNetworkFeedVideoId);

        if (null === $feedVideo) {
            return;
        }

        $video = $feedVideo->video;

        if (null === $video) {
            $video = new Video();
            $video->sourceUri = $command->sourceUri;
            $video->title = $this->buildTitle($feedVideo);
            $video->width = $feedVideo->width;
            $video->height = $feedVideo->height;
            $feedVideo->video = $video;
            $this->entityManager->persist($video);
            $this->entityManager->flush();
        }

        if (VideoStatusEnum::READY === $video->status) {
            return;
        }

        if (VideoStatusEnum::PROCESSING === $video->status && null !== $video->transcodingJobName) {
            $this->launcher->scheduleStatusPoll($video);

            return;
        }

        if (null === $video->originalPath) {
            $destinationPath = 'scraper-source/videos/'.$video->getUuid()->toRfc4122().'.mp4';

            try {
                $this->archiver->archive($command->sourceUri, $destinationPath);
            } catch (\InvalidArgumentException $exception) {
                $video->status = VideoStatusEnum::FAILED;
                $video->failureReason = 'Source archiving rejected: '.$exception->getMessage();
                $this->entityManager->flush();

                $this->logger->error('[Video transcode] permanent archive failure.', ['source_uri' => $command->sourceUri, 'error' => $exception->getMessage()]);

                return;
            }

            $video->originalPath = $destinationPath;
            $this->entityManager->flush();
        }

        try {
            $this->launcher->launch($video, \sprintf('gs://%s/%s', $this->gcloudBucket, $video->originalPath));
        } catch (TranscoderAtCapacityException $exception) {
            $this->capacityDeferral->deferOrFail($command, $video, $exception);
        }
    }

    private function buildTitle(SocialNetworkFeedVideo $feedVideo): string
    {
        $source = trim($feedVideo->feed->description ?? $feedVideo->feed->username ?? '');

        if ('' === $source) {
            $source = 'Video';
        }

        return mb_substr($source, 0, 100);
    }
}
