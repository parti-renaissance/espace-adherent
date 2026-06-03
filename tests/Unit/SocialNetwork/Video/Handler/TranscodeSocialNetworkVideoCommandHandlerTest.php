<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Video\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Repository\SocialNetworkFeedVideoRepository;
use App\SocialNetwork\Video\Command\TranscodeSocialNetworkVideoCommand;
use App\SocialNetwork\Video\Handler\TranscodeSocialNetworkVideoCommandHandler;
use App\Video\Storage\VideoSourceArchiverInterface;
use App\Video\Transcoding\Exception\TranscoderAtCapacityException;
use App\Video\Transcoding\TranscoderCapacityDeferral;
use App\Video\Transcoding\VideoTranscodingLauncher;
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
    private SocialNetworkFeedVideoRepository&MockObject $feedVideoRepository;
    private VideoSourceArchiverInterface&MockObject $archiver;
    private VideoTranscodingLauncher&MockObject $launcher;
    private TranscoderCapacityDeferral $capacityDeferral;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        // Providers (no interaction assertion) are stubs; verified collaborators are mocks. The deferral
        // is a stub here and replaced by a mock only in the test that verifies it is invoked.
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->feedVideoRepository = $this->createMock(SocialNetworkFeedVideoRepository::class);
        $this->archiver = $this->createMock(VideoSourceArchiverInterface::class);
        $this->launcher = $this->createMock(VideoTranscodingLauncher::class);
        $this->capacityDeferral = $this->createStub(TranscoderCapacityDeferral::class);
        $this->logger = $this->createStub(LoggerInterface::class);
    }

    public function testCreatesOwnVideoArchivesSourceAndLaunches(): void
    {
        $feedVideo = $this->feedVideo('campaign post');
        $this->expectFeedVideoLookup($feedVideo);

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

    public function testExistingReadyVideoIsNotRelaunched(): void
    {
        $feedVideo = $this->feedVideo('post');
        $feedVideo->video = $this->video(VideoStatusEnum::READY, 'scraper-source/videos/abc.mp4');
        $this->expectFeedVideoLookup($feedVideo);

        $this->archiver->expects(self::never())->method('archive');
        $this->launcher->expects(self::never())->method('launch');

        $this->handle();

        self::assertSame(VideoStatusEnum::READY, $feedVideo->video->status);
    }

    public function testProcessingVideoWithoutOriginalPathArchivesAndLaunches(): void
    {
        $feedVideo = $this->feedVideo('post');
        $existing = $this->video(VideoStatusEnum::PROCESSING, null);
        $feedVideo->video = $existing;
        $this->expectFeedVideoLookup($feedVideo);

        $this->archiver->expects(self::once())->method('archive')->with(self::SOURCE, self::anything());
        $this->launcher->expects(self::once())->method('launch')->with($existing, self::anything());

        $this->handle();
    }

    public function testProcessingVideoWithRunningJobRearmsPollWithoutDuplicating(): void
    {
        $feedVideo = $this->feedVideo('post');
        $existing = $this->video(VideoStatusEnum::PROCESSING, 'scraper-source/videos/abc.mp4');
        $existing->transcodingJobName = 'projects/1/locations/europe-west1/jobs/running';
        $feedVideo->video = $existing;
        $this->expectFeedVideoLookup($feedVideo);

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

    public function testAtCapacityDelegatesToDeferral(): void
    {
        $feedVideo = $this->feedVideo('post');
        $existing = $this->video(VideoStatusEnum::PENDING, 'scraper-source/videos/abc.mp4');
        $feedVideo->video = $existing;
        $this->expectFeedVideoLookup($feedVideo);

        $this->archiver->expects(self::never())->method('archive');
        $this->launcher
            ->expects(self::once())
            ->method('launch')
            ->with($existing, self::anything())
            ->willThrowException(TranscoderAtCapacityException::reactive());

        $deferral = $this->createMock(TranscoderCapacityDeferral::class);
        $deferral
            ->expects(self::once())
            ->method('deferOrFail')
            ->with(
                self::isInstanceOf(TranscodeSocialNetworkVideoCommand::class),
                $existing,
                self::isInstanceOf(TranscoderAtCapacityException::class),
            );

        $this->handle($deferral);

        // The handler swallows the exception (acks the message); the deferral owns the next step.
        self::assertSame(VideoStatusEnum::PENDING, $existing->status);
    }

    public function testMissingFeedVideoIsANoOp(): void
    {
        $this->feedVideoRepository->expects(self::once())->method('find')->with(self::SNFV_ID)->willReturn(null);

        $this->archiver->expects(self::never())->method('archive');
        $this->launcher->expects(self::never())->method('launch');

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

    private function handle(?TranscoderCapacityDeferral $capacityDeferral = null): void
    {
        $handler = new TranscodeSocialNetworkVideoCommandHandler(
            $this->entityManager,
            $this->feedVideoRepository,
            $this->archiver,
            $this->launcher,
            $capacityDeferral ?? $this->capacityDeferral,
            $this->logger,
            self::BUCKET,
        );

        $handler(new TranscodeSocialNetworkVideoCommand(self::SNFV_ID, self::SOURCE));
    }
}
