<?php

declare(strict_types=1);

namespace Tests\App\Controller\Webhook;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Repository\SocialNetworkFeedRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class SocialNetworkFeedWebhookControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private const VALID_URL = '/social-network-feed/social-network-feed-webhook-key';

    public function testValidKeyPersistsFeedWithMedia(): void
    {
        $payload = [
            'id' => 987654,
            'post_id' => 'tweet-1',
            'platform' => 'twitter',
            'username' => 'renaissance',
            'description' => 'hello world',
            'date_published' => '2026-05-07T13:46:16.384Z',
            'image_url' => 'https://cdn/img.jpg',
            'avatar_image_url' => 'https://cdn/avatar.jpg',
            'url' => 'https://twitter.com/tweet-1',
            'score' => 5,
            'videos' => [
                ['id' => 1, 'video_type' => 'video', 'width' => 1280, 'height' => 720, 'bitrate' => 3000, 'stream_url' => 'https://cdn/s.m3u8'],
            ],
            'photos' => [
                ['id' => 2, 'width' => 640, 'height' => 480, 'src' => 'https://cdn/p.jpg'],
            ],
            'raw_json' => ['k' => 'v'],
        ];

        $this->postJson(self::VALID_URL, $payload);

        self::assertResponseIsSuccessful();

        $feed = $this->getFeedRepository()->findOneByScraperId(987654);

        self::assertInstanceOf(SocialNetworkFeed::class, $feed);
        self::assertSame('tweet-1', $feed->postId);
        self::assertSame('twitter', $feed->platform);
        self::assertSame('renaissance', $feed->username);
        self::assertSame(5, $feed->score);
        self::assertNotNull($feed->datePublished);
        self::assertSame('2026-05-07', $feed->datePublished->format('Y-m-d'));
        self::assertCount(1, $feed->videos);
        self::assertSame('https://cdn/s.m3u8', $feed->videos->first()->streamUrl);
        self::assertCount(1, $feed->photos);
        self::assertSame('https://cdn/p.jpg', $feed->photos->first()->src);
    }

    public function testValidKeyArchivesFeedImages(): void
    {
        $imageUrl = 'gs://re-social-posts-rs-scrapper-staging-content/bronze/twitter/1/media/img.jpg';
        $avatarUrl = 'gs://re-social-posts-rs-scrapper-staging-content/bronze/twitter/1/media/avatar.jpg';
        $photoSrc = 'gs://re-social-posts-rs-scrapper-staging-content/bronze/twitter/1/media/photo.jpg';

        $this->postJson(self::VALID_URL, [
            'id' => 778899,
            'post_id' => 'tweet-archive',
            'platform' => 'twitter',
            'image_url' => $imageUrl,
            'avatar_image_url' => $avatarUrl,
            'photos' => [
                ['id' => 1, 'src' => $photoSrc],
            ],
        ]);

        self::assertResponseIsSuccessful();

        // Archiving messages are routed sync in tests and processed through the No-Op publisher.
        $feed = $this->getFeedRepository()->findOneByScraperId(778899);

        self::assertInstanceOf(SocialNetworkFeed::class, $feed);
        self::assertSame('social-feed/'.sha1($imageUrl).'.jpg', $feed->publicImagePath);
        self::assertSame('social-feed/'.sha1($avatarUrl).'.jpg', $feed->publicAvatarImagePath);
        self::assertCount(1, $feed->photos);
        self::assertSame('social-feed/'.sha1($photoSrc).'.jpg', $feed->photos->first()->publicSrc);
    }

    public function testInvalidKeyDoesNotPersist(): void
    {
        $this->postJson('/social-network-feed/wrong-key', [
            'id' => 111222,
            'post_id' => 'x',
            'platform' => 'twitter',
        ]);

        self::assertResponseIsSuccessful();
        self::assertNull($this->getFeedRepository()->findOneByScraperId(111222));
    }

    public function testInvalidJsonBodyDoesNotPersist(): void
    {
        $countBefore = $this->getFeedRepository()->count([]);

        $this->client->request(
            Request::METHOD_POST,
            self::VALID_URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'not-a-json-object',
        );

        self::assertResponseIsSuccessful();
        self::assertSame($countBefore, $this->getFeedRepository()->count([]));
    }

    private function postJson(string $url, array $payload): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload),
        );
    }

    private function getFeedRepository(): SocialNetworkFeedRepository
    {
        return static::getContainer()->get(SocialNetworkFeedRepository::class);
    }
}
