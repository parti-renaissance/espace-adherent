<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Image\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedImagesCommand;
use App\SocialNetwork\Image\Handler\PublishSocialNetworkFeedImagesCommandHandler;
use App\SocialNetwork\Image\Storage\FeedImagePublisherInterface;
use App\SocialNetwork\Image\Storage\PublishedImage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PublishSocialNetworkFeedImagesCommandHandlerTest extends TestCase
{
    public function testArchivesImageAndAvatarSetsPathsAndFlushes(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'gs://scraper-a/img.jpg';
        $feed->avatarImageUrl = 'gs://scraper-a/avatar.jpg';

        $publisher = $this->createMock(FeedImagePublisherInterface::class);
        $publisher->method('expectedPath')->willReturnCallback(function (string $source): string {
            return 'expected:'.$source;
        });
        $publisher->expects(self::exactly(2))->method('publish')->willReturnCallback(function (string $source): PublishedImage {
            return new PublishedImage('published:'.$source, 320, 240);
        });

        $this->handle($feed, $publisher, $this->createStub(LoggerInterface::class));

        self::assertSame('published:gs://scraper-a/img.jpg', $feed->publicImagePath);
        self::assertSame(320, $feed->imageWidth);
        self::assertSame(240, $feed->imageHeight);
        self::assertSame('published:gs://scraper-a/avatar.jpg', $feed->publicAvatarImagePath);
        self::assertSame(320, $feed->avatarWidth);
        self::assertSame(240, $feed->avatarHeight);
    }

    public function testSkipsWhenAlreadyPublished(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'gs://scraper-a/img.jpg';
        $feed->publicImagePath = 'expected:gs://scraper-a/img.jpg';

        $publisher = $this->createMock(FeedImagePublisherInterface::class);
        $publisher->method('expectedPath')->willReturnCallback(function (string $source): string {
            return 'expected:'.$source;
        });
        $publisher->expects(self::never())->method('publish');

        $this->handle($feed, $publisher, $this->createStub(LoggerInterface::class));

        self::assertSame('expected:gs://scraper-a/img.jpg', $feed->publicImagePath);
    }

    public function testPermanentFailureIsLoggedAndLeavesPathNull(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'gs://scraper-a/img.jpg';

        $publisher = $this->createMock(FeedImagePublisherInterface::class);
        $publisher->method('expectedPath')->willReturn('expected');
        $publisher->expects(self::once())->method('publish')->willThrowException(new \InvalidArgumentException('not an image'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $this->handle($feed, $publisher, $logger);

        self::assertNull($feed->publicImagePath);
    }

    public function testReturnsWhenFeedNotFound(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->willReturn(null);
        $entityManager->expects(self::never())->method('flush');

        $publisher = $this->createMock(FeedImagePublisherInterface::class);
        $publisher->expects(self::never())->method('publish');

        $handler = new PublishSocialNetworkFeedImagesCommandHandler($entityManager, $publisher, $this->createStub(LoggerInterface::class));
        $handler(new PublishSocialNetworkFeedImagesCommand(404));
    }

    private function handle(SocialNetworkFeed $feed, FeedImagePublisherInterface $publisher, LoggerInterface $logger): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->with(SocialNetworkFeed::class, 42)->willReturn($feed);
        $entityManager->expects(self::once())->method('flush');

        $handler = new PublishSocialNetworkFeedImagesCommandHandler($entityManager, $publisher, $logger);
        $handler(new PublishSocialNetworkFeedImagesCommand(42));
    }
}
