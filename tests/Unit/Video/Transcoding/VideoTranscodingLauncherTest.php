<?php

declare(strict_types=1);

namespace Tests\App\Unit\Video\Transcoding;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Repository\VideoRepository;
use App\Video\Transcoding\Exception\TranscoderAtCapacityException;
use App\Video\Transcoding\VideoTranscoderInterface;
use App\Video\Transcoding\VideoTranscodingLauncher;
use Doctrine\ORM\EntityManagerInterface;
use Google\ApiCore\ApiException;
use Google\ApiCore\ApiStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final class VideoTranscodingLauncherTest extends TestCase
{
    private const string UUID = '7ebd8666-8059-4bf2-afcd-8d08ade88af3';
    private const string BUCKET = 'videos-out';
    private const string INPUT = 'gs://source/videos/x.mp4';
    private const int THRESHOLD = 15;

    private VideoTranscoderInterface&MockObject $transcoder;
    private EntityManagerInterface&MockObject $entityManager;
    private MessageBusInterface&MockObject $bus;
    private VideoRepository&MockObject $videoRepository;

    protected function setUp(): void
    {
        $this->transcoder = $this->createMock(VideoTranscoderInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->videoRepository = $this->createMock(VideoRepository::class);
    }

    public function testGateOpenLaunchesJob(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $this->expectActiveJobCount($video, 3);

        $this->transcoder
            ->expects(self::once())
            ->method('createJob')
            ->with(self::INPUT, 'gs://'.self::BUCKET.'/videos/'.self::UUID.'/', self::UUID, true)
            ->willReturn('jobs/created');

        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::once())->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $this->launcher()->launch($video, self::INPUT);

        self::assertSame(VideoStatusEnum::PROCESSING, $video->status);
        self::assertSame('jobs/created', $video->transcodingJobName);
        self::assertInstanceOf(\DateTimeImmutable::class, $video->transcodingStartedAt);
        self::assertNull($video->transcodingFinishedAt);
    }

    public function testGateClosedThrowsAtCapacityWithoutCreatingJob(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $this->expectActiveJobCount($video, self::THRESHOLD);

        $this->transcoder->expects(self::never())->method('createJob');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        try {
            $this->launcher()->launch($video, self::INPUT);
            self::fail('Expected TranscoderAtCapacityException.');
        } catch (TranscoderAtCapacityException $exception) {
            self::assertSame(TranscoderAtCapacityException::CAUSE_PROACTIVE, $exception->cause);
            self::assertSame(self::THRESHOLD, $exception->activeJobCount);
        }

        self::assertSame(VideoStatusEnum::PENDING, $video->status);
    }

    public function testThresholdZeroDisablesGate(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));

        $this->videoRepository->expects(self::never())->method('countActiveTranscodingJobs');

        $this->transcoder
            ->expects(self::once())
            ->method('createJob')
            ->with(self::INPUT, 'gs://'.self::BUCKET.'/videos/'.self::UUID.'/', self::UUID, true)
            ->willReturn('jobs/created');

        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::once())->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $this->launcher(0)->launch($video, self::INPUT);

        self::assertSame(VideoStatusEnum::PROCESSING, $video->status);
    }

    public function testResourceExhaustedMapsToAtCapacity(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $this->expectActiveJobCount($video, 3);

        $this->transcoder
            ->expects(self::once())
            ->method('createJob')
            ->willThrowException(new ApiException('quota exceeded', 8, ApiStatus::RESOURCE_EXHAUSTED));

        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        try {
            $this->launcher()->launch($video, self::INPUT);
            self::fail('Expected TranscoderAtCapacityException.');
        } catch (TranscoderAtCapacityException $exception) {
            self::assertSame(TranscoderAtCapacityException::CAUSE_REACTIVE, $exception->cause);
            self::assertNull($exception->activeJobCount);
        }

        self::assertSame(VideoStatusEnum::PENDING, $video->status);
    }

    public function testTransientNonResourceExhaustedStillRethrows(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $this->expectActiveJobCount($video, 3);

        $this->transcoder
            ->expects(self::once())
            ->method('createJob')
            ->willThrowException(new ApiException('backend unavailable', 14, ApiStatus::UNAVAILABLE));

        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        $this->expectException(ApiException::class);

        $this->launcher()->launch($video, self::INPUT);
    }

    public function testPermanentCreateJobFailureMarksVideoFailedWithoutRetry(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $this->expectActiveJobCount($video, 3);

        $this->transcoder
            ->expects(self::once())
            ->method('createJob')
            ->with(self::INPUT, 'gs://'.self::BUCKET.'/videos/'.self::UUID.'/', self::UUID, true)
            ->willThrowException(new ApiException('invalid job config', 3, ApiStatus::INVALID_ARGUMENT));

        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        $this->launcher()->launch($video, self::INPUT);

        self::assertSame(VideoStatusEnum::FAILED, $video->status);
        self::assertSame('invalid job config', $video->failureReason);
    }

    private function expectActiveJobCount(Video $video, int $count): void
    {
        $this->videoRepository
            ->expects(self::once())
            ->method('countActiveTranscodingJobs')
            ->with($video)
            ->willReturn($count);
    }

    private function launcher(int $threshold = self::THRESHOLD): VideoTranscodingLauncher
    {
        return new VideoTranscodingLauncher(
            $this->transcoder,
            $this->entityManager,
            $this->bus,
            $this->videoRepository,
            self::BUCKET,
            $threshold,
        );
    }
}
