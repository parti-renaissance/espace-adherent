<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Publication;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Repository\SocialNetworkFeedRepository;
use App\SocialNetwork\Publication\SocialNetworkFeedReadinessChecker;
use PHPUnit\Framework\TestCase;

final class SocialNetworkFeedReadinessCheckerTest extends TestCase
{
    public function testReadyWhenNoMedia(): void
    {
        self::assertTrue($this->createChecker()->isReadyToPublish(new SocialNetworkFeed()));
    }

    public function testNotReadyWhenMainImageNotCopied(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'https://cdn/img.jpg';

        self::assertFalse($this->createChecker()->isReadyToPublish($feed));
    }

    public function testReadyWhenMainImageCopied(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'https://cdn/img.jpg';
        $feed->publicImagePath = 'social-feed/img.jpg';

        self::assertTrue($this->createChecker()->isReadyToPublish($feed));
    }

    public function testNotReadyWhenAvatarNotCopied(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->avatarImageUrl = 'https://cdn/avatar.jpg';

        self::assertFalse($this->createChecker()->isReadyToPublish($feed));
    }

    public function testNotReadyWhenPhotosNotCopied(): void
    {
        self::assertFalse($this->createChecker(uncopiedPhotos: 1)->isReadyToPublish(new SocialNetworkFeed()));
    }

    public function testNotReadyWhenVideosNotTranscoded(): void
    {
        self::assertFalse($this->createChecker(untranscodedVideos: 1)->isReadyToPublish(new SocialNetworkFeed()));
    }

    private function createChecker(int $uncopiedPhotos = 0, int $untranscodedVideos = 0): SocialNetworkFeedReadinessChecker
    {
        $repository = $this->createStub(SocialNetworkFeedRepository::class);
        $repository->method('countUncopiedPhotos')->willReturn($uncopiedPhotos);
        $repository->method('countUntranscodedVideos')->willReturn($untranscodedVideos);

        return new SocialNetworkFeedReadinessChecker($repository);
    }
}
