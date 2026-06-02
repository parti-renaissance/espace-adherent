<?php

declare(strict_types=1);

namespace App\SocialNetwork\Webhook\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPhoto;
use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Repository\SocialNetworkFeedRepository;
use App\SocialNetwork\Video\Command\TranscodeSocialNetworkVideoCommand;
use App\SocialNetwork\Webhook\Command\SocialNetworkFeedWebhookCommand;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class SocialNetworkFeedWebhookCommandHandler
{
    public function __construct(
        private readonly SocialNetworkFeedRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(SocialNetworkFeedWebhookCommand $command): void
    {
        $payload = $command->getPayload();

        if (!isset($payload['id'], $payload['post_id'], $payload['platform'])) {
            $this->logger->error('[SocialNetworkFeed webhook] missing required fields, message skipped.', ['payload' => $payload]);

            return;
        }

        $scraperId = (int) $payload['id'];
        $feed = $this->repository->findOneByScraperId($scraperId) ?? new SocialNetworkFeed();

        $this->hydrateFeed($feed, $payload, $scraperId);

        try {
            $this->entityManager->persist($feed);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            // Concurrent delivery of the same post: another worker inserted it first.
            // Rethrow so Messenger retries; the retry will find the row and update it.
            $this->logger->info('[SocialNetworkFeed webhook] concurrent insert, will retry as update.', ['scraper_id' => $scraperId]);

            throw $exception;
        }

        $this->dispatchTranscoding($feed);
    }

    private function dispatchTranscoding(SocialNetworkFeed $feed): void
    {
        foreach ($feed->videos as $video) {
            if (null === $video->streamUrl) {
                continue;
            }

            $this->bus->dispatch(new TranscodeSocialNetworkVideoCommand($video->id, $video->streamUrl));
        }
    }

    private function hydrateFeed(SocialNetworkFeed $feed, array $payload, int $scraperId): void
    {
        $feed->scraperId = $scraperId;
        $feed->postId = (string) $payload['post_id'];
        $feed->platform = (string) $payload['platform'];
        $feed->username = $payload['username'] ?? null;
        $feed->description = $payload['description'] ?? null;
        $feed->imageUrl = $payload['image_url'] ?? null;
        $feed->avatarImageUrl = $payload['avatar_image_url'] ?? null;
        $feed->url = $payload['url'] ?? null;
        $feed->score = isset($payload['score']) ? (int) $payload['score'] : null;
        $feed->datePublished = $this->parseDate($payload['date_published'] ?? null);

        $feed->rawJson = $payload;

        $feed->clearVideos();
        foreach ($payload['videos'] ?? [] as $videoData) {
            if (!\is_array($videoData)) {
                continue;
            }

            $video = new SocialNetworkFeedVideo($feed);
            $video->scraperId = isset($videoData['id']) ? (int) $videoData['id'] : null;
            $video->videoType = $videoData['video_type'] ?? null;
            $video->width = isset($videoData['width']) ? (int) $videoData['width'] : null;
            $video->height = isset($videoData['height']) ? (int) $videoData['height'] : null;
            $video->bitrate = isset($videoData['bitrate']) ? (int) $videoData['bitrate'] : null;
            $video->streamUrl = $videoData['stream_url'] ?? null;
            $feed->addVideo($video);
        }

        $feed->clearPhotos();
        foreach ($payload['photos'] ?? [] as $photoData) {
            if (!\is_array($photoData)) {
                continue;
            }

            $photo = new SocialNetworkFeedPhoto($feed);
            $photo->scraperId = isset($photoData['id']) ? (int) $photoData['id'] : null;
            $photo->width = isset($photoData['width']) ? (int) $photoData['width'] : null;
            $photo->height = isset($photoData['height']) ? (int) $photoData['height'] : null;
            $photo->src = $photoData['src'] ?? null;
            $feed->addPhoto($photo);
        }
    }

    private function parseDate(?string $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            $this->logger->warning('[SocialNetworkFeed webhook] invalid date_published, stored as null.', ['value' => $value]);

            return null;
        }
    }
}
