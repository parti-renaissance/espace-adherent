<?php

declare(strict_types=1);

namespace Tests\App\Unit\Video\Transcoding\Handler;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Repository\VideoRepository;
use App\Video\Transcoding\Command\CheckVideoTranscodingStatusCommand;
use App\Video\Transcoding\Handler\CheckVideoTranscodingStatusCommandHandler;
use App\Video\Transcoding\TranscodedVideoProbeInterface;
use App\Video\Transcoding\TranscodingJobStatus;
use App\Video\Transcoding\VideoMediaInfo;
use App\Video\Transcoding\VideoTranscoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Uid\Uuid;

final class CheckVideoTranscodingStatusCommandHandlerTest extends TestCase
{
    private const string UUID = '7ebd8666-8059-4bf2-afcd-8d08ade88af3';
    private const string JOB = 'projects/1/locations/europe-west1/jobs/abc';

    private EntityManagerInterface&MockObject $entityManager;
    private VideoRepository&MockObject $videoRepository;
    private VideoTranscoderInterface&MockObject $transcoder;
    private MessageBusInterface&MockObject $bus;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->videoRepository = $this->createMock(VideoRepository::class);
        $this->transcoder = $this->createMock(VideoTranscoderInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
    }

    public function testSucceededJobMarksVideoReadyWithMediaPath(): void
    {
        $video = $this->processingVideo(self::JOB);
        $this->expectVideoLookup($video);

        $this->transcoder
            ->expects(self::once())
            ->method('getJob')
            ->with(self::JOB)
            ->willReturn(new TranscodingJobStatus(VideoStatusEnum::READY));

        $probe = $this->createMock(TranscodedVideoProbeInterface::class);
        $probe->expects(self::once())->method('probe')->with($video)->willReturn(new VideoMediaInfo(720, 1280, 42));

        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        $this->handle(new CheckVideoTranscodingStatusCommand(self::UUID, self::JOB, time()), $probe);

        self::assertSame(VideoStatusEnum::READY, $video->status);
        self::assertSame('videos/'.self::UUID, $video->mediaPath);
        self::assertSame(720, $video->width);
        self::assertSame(1280, $video->height);
        self::assertSame(42, $video->duration);
    }

    public function testFailedJobStoresFailureReason(): void
    {
        $video = $this->processingVideo(self::JOB);
        $this->expectVideoLookup($video);

        $this->transcoder
            ->expects(self::once())
            ->method('getJob')
            ->with(self::JOB)
            ->willReturn(new TranscodingJobStatus(VideoStatusEnum::FAILED, error: 'codec not supported'));

        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        $this->handle(new CheckVideoTranscodingStatusCommand(self::UUID, self::JOB, time()));

        self::assertSame(VideoStatusEnum::FAILED, $video->status);
        self::assertSame('codec not supported', $video->failureReason);
    }

    public function testRunningJobBeforeDeadlineRedispatchesWithDelay(): void
    {
        $video = $this->processingVideo(self::JOB);
        $this->expectVideoLookup($video);
        $startedAt = time();

        $this->transcoder
            ->expects(self::once())
            ->method('getJob')
            ->with(self::JOB)
            ->willReturn(new TranscodingJobStatus(VideoStatusEnum::PROCESSING));

        $this->entityManager->expects(self::never())->method('flush');
        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(static fn (CheckVideoTranscodingStatusCommand $message): bool => self::UUID === $message->videoUuid
                    && self::JOB === $message->jobName
                    && $startedAt === $message->startedAt),
                self::callback(static fn (array $stamps): bool => 1 === \count($stamps)
                    && $stamps[0] instanceof DelayStamp
                    && 30000 === $stamps[0]->getDelay()),
            )
            ->willReturn(new Envelope(new \stdClass()));

        $this->handle(new CheckVideoTranscodingStatusCommand(self::UUID, self::JOB, $startedAt));

        self::assertSame(VideoStatusEnum::PROCESSING, $video->status);
    }

    public function testRunningJobAfterDeadlineMarksFailedTimeout(): void
    {
        $video = $this->processingVideo(self::JOB);
        $this->expectVideoLookup($video);

        $this->transcoder
            ->expects(self::once())
            ->method('getJob')
            ->with(self::JOB)
            ->willReturn(new TranscodingJobStatus(VideoStatusEnum::PROCESSING));

        $this->entityManager->expects(self::once())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        $this->handle(new CheckVideoTranscodingStatusCommand(self::UUID, self::JOB, time() - 4000));

        self::assertSame(VideoStatusEnum::FAILED, $video->status);
        self::assertStringContainsString('timed out', (string) $video->failureReason);
    }

    public function testStaleJobNameIsIgnored(): void
    {
        $video = $this->processingVideo('a-newer-job');
        $this->expectVideoLookup($video);

        $this->transcoder->expects(self::never())->method('getJob');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');

        $this->handle(new CheckVideoTranscodingStatusCommand(self::UUID, self::JOB, time()));

        self::assertSame(VideoStatusEnum::PROCESSING, $video->status);
    }

    private function processingVideo(string $jobName): Video
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $video->status = VideoStatusEnum::PROCESSING;
        $video->transcodingJobName = $jobName;

        return $video;
    }

    private function expectVideoLookup(Video $video): void
    {
        $this->videoRepository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with(self::UUID)
            ->willReturn($video);
    }

    private function handle(CheckVideoTranscodingStatusCommand $command, ?TranscodedVideoProbeInterface $probe = null): void
    {
        $probe ??= $this->createStub(TranscodedVideoProbeInterface::class);

        (new CheckVideoTranscodingStatusCommandHandler($this->entityManager, $this->videoRepository, $this->transcoder, $probe, $this->bus))($command);
    }
}
