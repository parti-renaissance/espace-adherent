<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Webhook\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Repository\SocialNetworkFeedRepository;
use App\SocialNetwork\Video\Command\TranscodeSocialNetworkVideoCommand;
use App\SocialNetwork\Webhook\Command\SocialNetworkFeedWebhookCommand;
use App\SocialNetwork\Webhook\Handler\SocialNetworkFeedWebhookCommandHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SocialNetworkFeedWebhookCommandHandlerTest extends TestCase
{
    private SocialNetworkFeedRepository&MockObject $repository;
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;
    private MessageBusInterface&MockObject $bus;
    private SocialNetworkFeedWebhookCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SocialNetworkFeedRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->handler = new SocialNetworkFeedWebhookCommandHandler(
            $this->repository,
            $this->entityManager,
            $this->logger,
            $this->bus,
        );
    }

    public function testCreatesFeedFromCompletePayload(): void
    {
        $this->logger->expects(self::never())->method('error');

        $this->repository
            ->expects(self::once())
            ->method('findOneByScraperId')
            ->with(12345)
            ->willReturn(null)
        ;

        $persisted = null;
        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(function (SocialNetworkFeed $feed) use (&$persisted): bool {
                $persisted = $feed;

                return true;
            }))
        ;
        $this->entityManager->expects(self::once())->method('flush')
            ->willReturnCallback(function () use (&$persisted): void {
                foreach ($persisted->videos as $video) {
                    $video->id ??= 1;
                }
            });
        $this->bus->expects(self::once())->method('dispatch')->willReturnCallback(static fn (object $message) => new Envelope($message));

        ($this->handler)(new SocialNetworkFeedWebhookCommand($this->completePayload()));

        self::assertInstanceOf(SocialNetworkFeed::class, $persisted);
        self::assertSame(12345, $persisted->scraperId);
        self::assertSame('post-123', $persisted->postId);
        self::assertSame('twitter', $persisted->platform);
        self::assertSame('john', $persisted->username);
        self::assertSame('hello world', $persisted->description);
        self::assertSame('https://cdn/img.jpg', $persisted->imageUrl);
        self::assertSame('https://cdn/avatar.jpg', $persisted->avatarImageUrl);
        self::assertSame('https://twitter.com/post', $persisted->url);
        self::assertSame(3, $persisted->score);
        self::assertInstanceOf(\DateTimeImmutable::class, $persisted->datePublished);
        self::assertSame('2026-05-07', $persisted->datePublished->format('Y-m-d'));

        self::assertCount(1, $persisted->videos);
        $video = $persisted->videos->first();
        self::assertSame(10, $video->scraperId);
        self::assertSame('video', $video->videoType);
        self::assertSame(1920, $video->width);
        self::assertSame(1080, $video->height);
        self::assertSame(4500, $video->bitrate);
        self::assertSame('https://cdn/stream.m3u8', $video->streamUrl);
        self::assertSame($persisted, $video->feed);

        self::assertCount(1, $persisted->photos);
        $photo = $persisted->photos->first();
        self::assertSame(20, $photo->scraperId);
        self::assertSame(800, $photo->width);
        self::assertSame(600, $photo->height);
        self::assertSame('https://cdn/photo.jpg', $photo->src);
        self::assertSame($persisted, $photo->feed);
    }

    public function testUpdatesExistingFeedAndReplacesMedia(): void
    {
        $existing = new SocialNetworkFeed();
        $existing->scraperId = 12345;
        $existing->postId = 'old-post';
        $existing->platform = 'twitter';
        // Pre-existing media from a previous delivery.
        $existing->addVideo(new SocialNetworkFeedVideo($existing));
        $existing->addVideo(new SocialNetworkFeedVideo($existing));
        self::assertCount(2, $existing->videos);

        $this->logger->expects(self::never())->method('error');

        $this->repository
            ->expects(self::once())
            ->method('findOneByScraperId')
            ->with(12345)
            ->willReturn($existing)
        ;

        $this->entityManager->expects(self::once())->method('persist')->with($existing);
        $this->entityManager->expects(self::once())->method('flush')
            ->willReturnCallback(function () use ($existing): void {
                foreach ($existing->videos as $video) {
                    $video->id ??= 1;
                }
            });
        $this->bus->expects(self::once())->method('dispatch')->willReturnCallback(static fn (object $message) => new Envelope($message));

        ($this->handler)(new SocialNetworkFeedWebhookCommand($this->completePayload()));

        // Old videos replaced by the single video of the new payload, no duplication.
        self::assertSame('post-123', $existing->postId);
        self::assertCount(1, $existing->videos);
        self::assertSame(10, $existing->videos->first()->scraperId);
        self::assertCount(1, $existing->photos);
    }

    public function testSkipsIncompletePayload(): void
    {
        $this->repository->expects(self::never())->method('findOneByScraperId');
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');
        $this->bus->expects(self::never())->method('dispatch');
        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('[SocialNetworkFeed webhook] missing required fields, message skipped.', self::anything())
        ;

        // 'id' is missing.
        ($this->handler)(new SocialNetworkFeedWebhookCommand([
            'post_id' => 'post-123',
            'platform' => 'twitter',
        ]));
    }

    public function testDispatchesTranscodeCommandForVideoWithStreamUrl(): void
    {
        $this->repository->expects(self::once())->method('findOneByScraperId')->with(12345)->willReturn(null);

        $persisted = null;
        $this->entityManager->expects(self::once())->method('persist')->willReturnCallback(function (SocialNetworkFeed $feed) use (&$persisted): void {
            $persisted = $feed;
        });
        $this->entityManager->expects(self::once())->method('flush')->willReturnCallback(function () use (&$persisted): void {
            foreach ($persisted->videos as $video) {
                $video->id ??= 1;
            }
        });
        $this->logger->expects(self::never())->method('error');

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (TranscodeSocialNetworkVideoCommand $command): bool => 1 === $command->socialNetworkFeedVideoId
                && 'https://cdn/stream.m3u8' === $command->sourceUri))
            ->willReturn(new Envelope(new \stdClass()));

        ($this->handler)(new SocialNetworkFeedWebhookCommand($this->completePayload()));
    }

    private function completePayload(): array
    {
        return [
            'id' => 12345,
            'post_id' => 'post-123',
            'platform' => 'twitter',
            'username' => 'john',
            'description' => 'hello world',
            'date_published' => '2026-05-07T13:46:16.384Z',
            'image_url' => 'https://cdn/img.jpg',
            'avatar_image_url' => 'https://cdn/avatar.jpg',
            'url' => 'https://twitter.com/post',
            'score' => 3,
            'videos' => [
                [
                    'id' => 10,
                    'video_type' => 'video',
                    'width' => 1920,
                    'height' => 1080,
                    'bitrate' => 4500,
                    'stream_url' => 'https://cdn/stream.m3u8',
                ],
            ],
            'photos' => [
                [
                    'id' => 20,
                    'width' => 800,
                    'height' => 600,
                    'src' => 'https://cdn/photo.jpg',
                ],
            ],
            'raw_json' => ['foo' => 'bar'],
        ];
    }
}
