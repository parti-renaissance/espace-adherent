<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Image\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPhoto;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedPhotoCommand;
use App\SocialNetwork\Image\Handler\PublishSocialNetworkFeedPhotoCommandHandler;
use App\SocialNetwork\Image\Storage\FeedImagePublisherInterface;
use App\SocialNetwork\Image\Storage\PublishedImage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PublishSocialNetworkFeedPhotoCommandHandlerTest extends TestCase
{
    public function testArchivesPhotoSrcAndFlushes(): void
    {
        $photo = new SocialNetworkFeedPhoto(new SocialNetworkFeed());
        $photo->src = 'gs://scraper-a/photo.jpg';

        $publisher = $this->createMock(FeedImagePublisherInterface::class);
        $publisher->method('expectedPath')->willReturn('expected');
        $publisher->expects(self::once())->method('publish')->with('gs://scraper-a/photo.jpg')->willReturn(new PublishedImage('published/photo.jpg', 640, 480));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->with(SocialNetworkFeedPhoto::class, 7)->willReturn($photo);
        $entityManager->expects(self::once())->method('flush');

        $handler = new PublishSocialNetworkFeedPhotoCommandHandler($entityManager, $publisher, $this->createStub(LoggerInterface::class));
        $handler(new PublishSocialNetworkFeedPhotoCommand(7, 'gs://scraper-a/photo.jpg'));

        self::assertSame('published/photo.jpg', $photo->publicSrc);
        self::assertSame(640, $photo->width);
        self::assertSame(480, $photo->height);
    }

    public function testDoesNotOverwriteExistingPhotoDimensions(): void
    {
        $photo = new SocialNetworkFeedPhoto(new SocialNetworkFeed());
        $photo->src = 'gs://scraper-a/photo.jpg';
        $photo->width = 800;
        $photo->height = 600;

        $publisher = $this->createMock(FeedImagePublisherInterface::class);
        $publisher->method('expectedPath')->willReturn('expected');
        $publisher->expects(self::once())->method('publish')->willReturn(new PublishedImage('published/photo.jpg', 100, 100));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('find')->willReturn($photo);
        $entityManager->expects(self::once())->method('flush');

        $handler = new PublishSocialNetworkFeedPhotoCommandHandler($entityManager, $publisher, $this->createStub(LoggerInterface::class));
        $handler(new PublishSocialNetworkFeedPhotoCommand(7, 'gs://scraper-a/photo.jpg'));

        self::assertSame(800, $photo->width);
        self::assertSame(600, $photo->height);
    }
}
