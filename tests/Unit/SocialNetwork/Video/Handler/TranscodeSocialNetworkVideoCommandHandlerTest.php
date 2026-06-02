<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Video\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Repository\SocialNetworkFeedVideoRepository;
use App\Repository\VideoRepository;
use App\SocialNetwork\Video\Command\TranscodeSocialNetworkVideoCommand;
use App\SocialNetwork\Video\Handler\TranscodeSocialNetworkVideoCommandHandler;
use App\Video\Storage\VideoSourceArchiverInterface;
use App\Video\Transcoding\VideoTranscodingLauncher;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class TranscodeSocialNetworkVideoCommandHandlerTest extends TestCase
{
    private const string SOURCE = 'gs://re-social-posts-rs-scrapper-staging-content/bronze/twitter/1/media/0.mp4';
    private const int SNFV_ID = 5;
    private const string BUCKET = 'app-bucket';

    private EntityManagerInterface $entityManager;
    private VideoRepository&MockObject $videoRepository;
    private SocialNetworkFeedVideoRepository&MockObject $feedVideoRepository;
    private VideoSourceArchiverInterface&MockObject $archiver;
    private VideoTranscodingLauncher&MockObject $launcher;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        // Providers (no interaction assertion) are stubs; verified collaborators are mocks.
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->videoRepository = $this->createMock(VideoRepository::class);
        $this->feedVideoRepository = $this->createMock(SocialNetworkFeedVideoRepository::class);
        $this->archiver = $this->createMock(VideoSourceArchiverInterface::class);
        $this->launcher = $this->createMock(VideoTranscodingLauncher::class);
        $this->logger = $this->createStub(LoggerInterface::class);
    }

    public function testCreatesVideoLinksItArchivesSourceAndLaunches(): void
    {
        $feedVideo = $this->feedVideo('campaign post');
        $this->expectFeedVideoLookup($feedVideo);
        $this->expectVideoLookup(null);

        $this->archiver
            ->expects(self::once())
            ->method('archive')
            ->with(
                self::SOURCE,
                self::callback(static fn (string $dest): bool => str_starts_with($dest, 'scraper-source/videos/') && str_ends_with($dest, '.mp4')),
            );

        $launched = null;
        $this->launcher
            ->expects(self::once())
            ->method('launch')
            ->with(
                self::callback(function (Video $video) use (&$launched): bool {
                    $launched = $video;

                    return self::SOURCE === $video->sourceUri
                        && '' !== (string) $video->title
                        && 720 === $video->width
                        && 1280 === $video->height;
                }),
                self::callback(static fn (string $input): bool => str_starts_with($input, 'gs://'.self::BUCKET.'/scraper-source/videos/')),
            );

        $this->handle();

        self::assertNotNull($launched);
        self::assertSame($launched, $feedVideo->video);
    }

    public function testExistingReadyVideoIsLinkedButNotRelaunched(): void
    {
        $feedVideo = $this->feedVideo('post');
        $existing = $this->video(VideoStatusEnum::READY, 'scraper-source/videos/abc.mp4');
        $this->expectFeedVideoLookup($feedVideo);
        $this->expectVideoLookup($existing);

        $this->archiver->expects(self::never())->method('archive');
        $this->launcher->expects(self::never())->method('launch');

        $this->handle();

        self::assertSame($existing, $feedVideo->video);
    }

    public function testProcessingVideoWithoutOriginalPathArchivesAndLaunches(): void
    {
        $feedVideo = $this->feedVideo('post');
        $existing = $this->video(VideoStatusEnum::PROCESSING, null);
        $this->expectFeedVideoLookup($feedVideo);
        $this->expectVideoLookup($existing);

        $this->archiver->expects(self::once())->method('archive')->with(self::SOURCE, self::anything());
        $this->launcher->expects(self::once())->method('launch')->with($existing, self::anything());

        $this->handle();
    }

    public function testProcessingVideoWithRunningJobRearmsPollWithoutDuplicating(): void
    {
        $feedVideo = $this->feedVideo('post');
        $existing = $this->video(VideoStatusEnum::PROCESSING, 'scraper-source/videos/abc.mp4');
        $existing->transcodingJobName = 'projects/1/locations/europe-west1/jobs/running';
        $this->expectFeedVideoLookup($feedVideo);
        $this->expectVideoLookup($existing);

        // No duplicate job, no re-archive: only the poll is re-armed.
        $this->archiver->expects(self::never())->method('archive');
        $this->launcher->expects(self::never())->method('launch');
        $this->launcher->expects(self::once())->method('scheduleStatusPoll')->with($existing);

        $this->handle();
    }

    public function testPermanentArchiveRejectionMarksVideoFailed(): void
    {
        $feedVideo = $this->feedVideo('post');
        $this->expectFeedVideoLookup($feedVideo);
        $this->expectVideoLookup(null);

        $this->archiver
            ->expects(self::once())
            ->method('archive')
            ->willThrowException(new \InvalidArgumentException('Source bucket "evil" is not in the allowed scraper buckets.'));

        $this->launcher->expects(self::never())->method('launch');

        $this->handle();

        self::assertNotNull($feedVideo->video);
        self::assertSame(VideoStatusEnum::FAILED, $feedVideo->video->status);
        self::assertStringContainsString('Source archiving rejected', (string) $feedVideo->video->failureReason);
    }

    public function testConcurrentCreationRethrowsToRetry(): void
    {
        $this->expectFeedVideoLookup($this->feedVideo('post'));
        $this->expectVideoLookup(null);

        $this->entityManager
            ->method('flush')
            ->willThrowException($this->createStub(UniqueConstraintViolationException::class));

        $this->archiver->expects(self::never())->method('archive');
        $this->launcher->expects(self::never())->method('launch');

        $this->expectException(UniqueConstraintViolationException::class);

        $this->handle();
    }

    private function expectFeedVideoLookup(SocialNetworkFeedVideo $feedVideo): void
    {
        $this->feedVideoRepository
            ->expects(self::once())
            ->method('find')
            ->with(self::SNFV_ID)
            ->willReturn($feedVideo);
    }

    private function expectVideoLookup(?Video $video): void
    {
        $this->videoRepository
            ->expects(self::once())
            ->method('findOneBySourceUri')
            ->with(self::SOURCE)
            ->willReturn($video);
    }

    private function feedVideo(string $description): SocialNetworkFeedVideo
    {
        $feed = new SocialNetworkFeed();
        $feed->description = $description;

        $feedVideo = new SocialNetworkFeedVideo($feed);
        $feedVideo->width = 720;
        $feedVideo->height = 1280;

        return $feedVideo;
    }

    private function video(VideoStatusEnum $status, ?string $originalPath): Video
    {
        $video = new Video();
        $video->sourceUri = self::SOURCE;
        $video->title = 'existing';
        $video->status = $status;
        $video->originalPath = $originalPath;

        return $video;
    }

    private function handle(): void
    {
        $handler = new TranscodeSocialNetworkVideoCommandHandler(
            $this->entityManager,
            $this->videoRepository,
            $this->feedVideoRepository,
            $this->archiver,
            $this->launcher,
            $this->logger,
            self::BUCKET,
        );

        $handler(new TranscodeSocialNetworkVideoCommand(self::SNFV_ID, self::SOURCE));
    }
}
