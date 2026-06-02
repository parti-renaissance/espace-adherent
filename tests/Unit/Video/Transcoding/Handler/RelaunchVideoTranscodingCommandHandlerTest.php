<?php

declare(strict_types=1);

namespace Tests\App\Unit\Video\Transcoding\Handler;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Repository\VideoRepository;
use App\Video\Transcoding\Command\RelaunchVideoTranscodingCommand;
use App\Video\Transcoding\Handler\RelaunchVideoTranscodingCommandHandler;
use App\Video\Transcoding\VideoTranscodingLauncher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

final class RelaunchVideoTranscodingCommandHandlerTest extends TestCase
{
    private const string UUID = '7ebd8666-8059-4bf2-afcd-8d08ade88af3';
    private const string BUCKET = 'gcloud-bucket';

    private VideoRepository&MockObject $videoRepository;
    private VideoTranscodingLauncher&MockObject $launcher;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->videoRepository = $this->createMock(VideoRepository::class);
        $this->launcher = $this->createMock(VideoTranscodingLauncher::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testFailedVideoWithArchiveIsRelaunchedFromOriginalPath(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $video->status = VideoStatusEnum::FAILED;
        $video->failureReason = 'codec not supported';
        $video->originalPath = 'scraper-source/videos/'.self::UUID.'.mp4';
        $this->expectVideoLookup($video);

        $this->launcher
            ->expects(self::once())
            ->method('launch')
            ->with($video, 'gs://'.self::BUCKET.'/scraper-source/videos/'.self::UUID.'.mp4');

        $this->logger->expects(self::never())->method('warning');

        $this->handle();

        self::assertNull($video->failureReason);
    }

    public function testProcessingVideoIsNotRelaunched(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $video->status = VideoStatusEnum::PROCESSING;
        $video->originalPath = 'scraper-source/videos/'.self::UUID.'.mp4';
        $this->expectVideoLookup($video);

        $this->launcher->expects(self::never())->method('launch');
        $this->logger->expects(self::never())->method('warning');

        $this->handle();
    }

    public function testVideoWithoutOriginalPathIsNotRelaunched(): void
    {
        $video = new Video(Uuid::fromString(self::UUID));
        $video->status = VideoStatusEnum::FAILED;
        $this->expectVideoLookup($video);

        $this->launcher->expects(self::never())->method('launch');
        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with('[Video relaunch] missing video or originalPath.', ['uuid' => self::UUID]);

        $this->handle();
    }

    public function testMissingVideoIsNotRelaunched(): void
    {
        $this->expectVideoLookup(null);

        $this->launcher->expects(self::never())->method('launch');
        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with('[Video relaunch] missing video or originalPath.', ['uuid' => self::UUID]);

        $this->handle();
    }

    private function expectVideoLookup(?Video $video): void
    {
        $this->videoRepository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with(self::UUID)
            ->willReturn($video);
    }

    private function handle(): void
    {
        (new RelaunchVideoTranscodingCommandHandler($this->videoRepository, $this->launcher, $this->logger, self::BUCKET))(
            new RelaunchVideoTranscodingCommand(self::UUID),
        );
    }
}
