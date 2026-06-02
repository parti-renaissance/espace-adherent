<?php

declare(strict_types=1);

namespace Tests\App\Controller\Webhook;

use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use Tests\App\AbstractWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * End-to-end wiring of the transcoding pipeline triggered by the scraper webhook. Messenger commands are
 * routed sync in test and GCP is faked (archiver no-op, transcoder reports READY immediately), so a single
 * POST runs webhook -> transcode -> status check up to a READY Video. Proves the FK hydration and the
 * sourceUri idempotency that the mocked unit tests cannot.
 */
class VideoTranscodingWebhookTest extends AbstractWebTestCase
{
    use ControllerTestTrait;

    private const string WEBHOOK_KEY = 'social-network-feed-webhook-key';

    public function testWebhookCreatesReadyVideoLinkedToFeedVideo(): void
    {
        $sourceUri = 'gs://re-social-posts-rs-scrapper-staging-content/bronze/twitter/post-a/media/0.mp4';

        $this->postVideoFeed(700001, 'post-a', $sourceUri);

        $video = $this->getRepository(Video::class)->findOneBy(['sourceUri' => $sourceUri]);
        self::assertNotNull($video, 'A Video should have been created from the scraper source.');
        self::assertSame(VideoStatusEnum::READY, $video->status);
        self::assertSame('videos/'.$video->getUuid()->toRfc4122(), $video->mediaPath);
        self::assertNotNull($video->originalPath);

        $feedVideo = $this->getRepository(SocialNetworkFeedVideo::class)->findOneBy(['streamUrl' => $sourceUri]);
        self::assertNotNull($feedVideo);
        self::assertNotNull($feedVideo->video, 'The feed video should point to the created Video.');
        self::assertSame($video->getUuid()->toRfc4122(), $feedVideo->video->getUuid()->toRfc4122());
    }

    public function testRedeliveryOfTheSameSourceDoesNotDuplicateVideo(): void
    {
        $sourceUri = 'gs://re-social-posts-rs-scrapper-staging-content/bronze/twitter/post-b/media/0.mp4';

        $this->postVideoFeed(700002, 'post-b', $sourceUri);
        $this->postVideoFeed(700002, 'post-b', $sourceUri);

        $videos = $this->getRepository(Video::class)->findBy(['sourceUri' => $sourceUri]);
        self::assertCount(1, $videos, 'Re-delivering the same source must reuse the existing Video (sourceUri idempotency).');
    }

    private function postVideoFeed(int $scraperId, string $postId, string $sourceUri): void
    {
        $this->client->jsonRequest('POST', '/social-network-feed/'.self::WEBHOOK_KEY, [
            'id' => $scraperId,
            'post_id' => $postId,
            'platform' => 'twitter',
            'username' => 'renaissance',
            'description' => 'Test video post',
            'videos' => [
                [
                    'id' => $scraperId + 1,
                    'stream_url' => $sourceUri,
                    'video_type' => 'mp4',
                    'width' => 720,
                    'height' => 1280,
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('webhook_renaissance_host'));
    }
}
