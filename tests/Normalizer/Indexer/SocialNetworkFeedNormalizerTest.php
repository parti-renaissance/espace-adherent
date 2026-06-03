<?php

declare(strict_types=1);

namespace Tests\App\Normalizer\Indexer;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPhoto;
use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Normalizer\Indexer\SocialNetworkFeedNormalizer;
use App\Utils\VideoUrlBuilder;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SocialNetworkFeedNormalizerTest extends TestCase
{
    public function testTextOnlyPostHasEmptyMediaAndNoImage(): void
    {
        $feed = $this->createFeed();
        $feed->username = 'renaissance';
        $feed->description = 'Hello';

        $result = $this->createNormalizer()->normalize($feed);

        self::assertSame('', $result['title']);
        self::assertSame('renaissance', $result['author']['username']);
        self::assertTrue($result['is_national']);
        self::assertNull($result['image']);
        self::assertSame(['network' => 'instagram', 'type' => 'text', 'items' => []], $result['media']);
    }

    public function testAuthorExposesNameAndUsernameButNoFirstLastName(): void
    {
        $feed = $this->createFeed();
        $feed->authorName = 'Renaissance Officiel';
        $feed->username = 'renaissance';

        $author = $this->createNormalizer()->normalize($feed)['author'];

        // name/username carry the social account identity; first/last name stay null (no Adherent author).
        self::assertSame('Renaissance Officiel', $author['name']);
        self::assertSame('renaissance', $author['username']);
        self::assertNull($author['first_name']);
        self::assertNull($author['last_name']);
    }

    public function testAuthorNameIsNullWhenAbsentFromFeed(): void
    {
        $author = $this->createNormalizer()->normalize($this->createFeed())['author'];

        self::assertNull($author['name']);
        self::assertNull($author['username']);
    }

    public function testSinglePhotoPostExposesImageAndPhotoType(): void
    {
        $feed = $this->createFeed();
        $feed->publicImagePath = 'social-feed/main.jpg';
        $feed->imageWidth = 1080;
        $feed->imageHeight = 1080;
        $this->addPhoto($feed, 'social-feed/p1.jpg');

        $result = $this->createNormalizer()->normalize($feed);

        self::assertSame(['url' => 'https://media.test/social-feed/main.jpg', 'width' => 1080, 'height' => 1080], $result['image']);
        self::assertSame('photo', $result['media']['type']);
        self::assertSame(
            [['type' => 'photo', 'url' => 'https://media.test/social-feed/p1.jpg', 'width' => 800, 'height' => 600]],
            $result['media']['items'],
        );
    }

    public function testMultiplePhotosProduceCarouselType(): void
    {
        $feed = $this->createFeed();
        $this->addPhoto($feed, 'social-feed/p1.jpg');
        $this->addPhoto($feed, 'social-feed/p2.jpg');

        $result = $this->createNormalizer()->normalize($feed);

        self::assertSame('photo_carousel', $result['media']['type']);
        self::assertCount(2, $result['media']['items']);
    }

    public function testOnlyReadyVideosAreIncluded(): void
    {
        $feed = $this->createFeed();
        $this->addVideo($feed, VideoStatusEnum::PROCESSING);
        $this->addVideo($feed, VideoStatusEnum::READY);

        $result = $this->createNormalizer()->normalize($feed);

        self::assertSame('video', $result['media']['type']);
        self::assertSame(
            [[
                'type' => 'video',
                'hls_url' => 'https://cdn.test/videos/abc/master.m3u8',
                'preview_url' => 'https://cdn.test/videos/abc/preview.mp4',
                'thumbnail_url' => 'https://cdn.test/videos/abc/thumbnail0000000000.jpeg',
                'width' => 1920,
                'height' => 1080,
                'duration' => 30,
            ]],
            $result['media']['items'],
        );
    }

    public function testMixedCarouselOrdersPhotosBeforeVideos(): void
    {
        $feed = $this->createFeed();
        $this->addPhoto($feed, 'social-feed/p1.jpg');
        $this->addVideo($feed, VideoStatusEnum::READY);

        $result = $this->createNormalizer()->normalize($feed);

        self::assertSame('carousel', $result['media']['type']);
        self::assertSame('photo', $result['media']['items'][0]['type']);
        self::assertSame('video', $result['media']['items'][1]['type']);
    }

    public function testNonHttpUrlIsRejected(): void
    {
        $feed = $this->createFeed();
        $feed->url = 'javascript:alert(1)';
        self::assertNull($this->createNormalizer()->normalize($feed)['url']);

        $feed->url = 'https://instagram.com/p/1';
        self::assertSame('https://instagram.com/p/1', $this->createNormalizer()->normalize($feed)['url']);
    }

    public function testTitleIsEmptyStringWhenNoUsername(): void
    {
        $feed = $this->createFeed();

        self::assertSame('', $this->createNormalizer()->normalize($feed)['title']);
    }

    public function testPhotoNotYetCopiedIsExcludedFromMedia(): void
    {
        $feed = $this->createFeed();
        $this->addPhoto($feed, null); // not copied to our bucket yet
        $this->addPhoto($feed, 'social-feed/p1.jpg');

        $result = $this->createNormalizer()->normalize($feed);

        self::assertSame('photo', $result['media']['type']);
        self::assertSame(
            [['type' => 'photo', 'url' => 'https://media.test/social-feed/p1.jpg', 'width' => 800, 'height' => 600]],
            $result['media']['items'],
        );
    }

    public function testAuthorImageUrlUsesCopiedAvatarWhenAvailable(): void
    {
        $feed = $this->createFeed();
        $feed->publicAvatarImagePath = 'social-feed/avatar.jpg';

        self::assertSame('https://media.test/social-feed/avatar.jpg', $this->createNormalizer()->normalize($feed)['author']['image_url']);
    }

    public function testAuthorImageUrlIsNullWithoutAvatar(): void
    {
        self::assertNull($this->createNormalizer()->normalize($this->createFeed())['author']['image_url']);
    }

    public function testCategoryIsCapitalizedPlatform(): void
    {
        $feed = $this->createFeed();
        $feed->platform = 'instagram';

        self::assertSame('Instagram', $this->createNormalizer()->normalize($feed)['category']);
    }

    private function createNormalizer(): SocialNetworkFeedNormalizer
    {
        // The injected service is a concrete League\Flysystem\Filesystem (publicUrl() lives there,
        // not on the FilesystemOperator interface), mirroring how UploadFileController consumes it.
        $mediaStorage = $this->createStub(Filesystem::class);
        $mediaStorage->method('publicUrl')->willReturnCallback(
            static fn (string $location): string => 'https://media.test/'.$location
        );

        return new SocialNetworkFeedNormalizer(
            $mediaStorage,
            new VideoUrlBuilder('https://cdn.test'),
            $this->createStub(UrlGeneratorInterface::class),
        );
    }

    private function createFeed(): SocialNetworkFeed
    {
        $feed = new SocialNetworkFeed();
        $feed->platform = 'instagram';

        return $feed;
    }

    private function addPhoto(SocialNetworkFeed $feed, ?string $publicSrc, int $width = 800, int $height = 600): void
    {
        $photo = new SocialNetworkFeedPhoto($feed);
        $photo->publicSrc = $publicSrc;
        $photo->width = $width;
        $photo->height = $height;
        $feed->addPhoto($photo);
    }

    private function addVideo(SocialNetworkFeed $feed, VideoStatusEnum $status): void
    {
        $video = new Video();
        $video->status = $status;
        $video->mediaPath = 'videos/abc';
        $video->width = 1920;
        $video->height = 1080;
        $video->duration = 30;

        $feedVideo = new SocialNetworkFeedVideo($feed);
        $feedVideo->video = $video;
        $feed->addVideo($feedVideo);
    }
}
