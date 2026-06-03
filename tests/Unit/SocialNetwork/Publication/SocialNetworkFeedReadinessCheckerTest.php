<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Publication;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPublicationFailure;
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

    public function testNoBlockingReasonWhenReady(): void
    {
        self::assertNull($this->createChecker()->getBlockingReason(new SocialNetworkFeed()));
    }

    public function testBlockingReasonPrioritisesVideoOverEverythingElse(): void
    {
        // Every requirement is blocked at once; video must win the priority order.
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'https://cdn/img.jpg';
        $feed->avatarImageUrl = 'https://cdn/avatar.jpg';

        $reason = $this->createChecker(uncopiedPhotos: 1, untranscodedVideos: 1)->getBlockingReason($feed);

        self::assertSame(SocialNetworkFeedPublicationFailure::VideoNotTranscoded, $reason);
    }

    public function testBlockingReasonIsPhotoWhenNoVideoBlocks(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'https://cdn/img.jpg';

        $reason = $this->createChecker(uncopiedPhotos: 1)->getBlockingReason($feed);

        self::assertSame(SocialNetworkFeedPublicationFailure::PhotoNotCopied, $reason);
    }

    public function testBlockingReasonIsImageWhenOnlyMainImageBlocks(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->imageUrl = 'https://cdn/img.jpg';
        $feed->avatarImageUrl = 'https://cdn/avatar.jpg';
        $feed->publicAvatarImagePath = 'social-feed/avatar.jpg'; // avatar copied, image not

        self::assertSame(
            SocialNetworkFeedPublicationFailure::ImageNotCopied,
            $this->createChecker()->getBlockingReason($feed),
        );
    }

    public function testBlockingReasonIsAvatarWhenOnlyAvatarBlocks(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->avatarImageUrl = 'https://cdn/avatar.jpg';

        self::assertSame(
            SocialNetworkFeedPublicationFailure::AvatarNotCopied,
            $this->createChecker()->getBlockingReason($feed),
        );
    }

    private function createChecker(int $uncopiedPhotos = 0, int $untranscodedVideos = 0): SocialNetworkFeedReadinessChecker
    {
        $repository = $this->createStub(SocialNetworkFeedRepository::class);
        $repository->method('countUncopiedPhotos')->willReturn($uncopiedPhotos);
        $repository->method('countUntranscodedVideos')->willReturn($untranscodedVideos);

        return new SocialNetworkFeedReadinessChecker($repository);
    }
}
