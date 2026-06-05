<?php

declare(strict_types=1);

namespace App\SocialNetwork\Webhook\Handler;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPhoto;
use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Repository\SocialNetworkFeedRepository;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedImagesCommand;
use App\SocialNetwork\Image\Command\PublishSocialNetworkFeedPhotoCommand;
use App\SocialNetwork\Publication\Command\CheckSocialNetworkFeedPublicationCommand;
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
        $this->dispatchImagePublishing($feed);

        $this->bus->dispatch(new CheckSocialNetworkFeedPublicationCommand($feed->getId(), time()));
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

    private function dispatchImagePublishing(SocialNetworkFeed $feed): void
    {
        // Only dispatch when there is uncopied media: a public path stays null until copied, and the
        // hydration/upsert above resets it whenever the source changes. Avoids no-op messages on
        // re-delivery of an already-copied post.
        if ((null !== $feed->imageUrl && null === $feed->publicImagePath)
            || (null !== $feed->avatarImageUrl && null === $feed->publicAvatarImagePath)
        ) {
            $this->bus->dispatch(new PublishSocialNetworkFeedImagesCommand($feed->getId()));
        }

        foreach ($feed->photos as $photo) {
            if (null === $photo->src || null !== $photo->publicSrc) {
                continue;
            }

            $this->bus->dispatch(new PublishSocialNetworkFeedPhotoCommand($photo->id, $photo->src));
        }
    }

    private function hydrateFeed(SocialNetworkFeed $feed, array $payload, int $scraperId): void
    {
        $authorName = trim($payload['full_name'] ?? '');
        if ('' === $authorName) {
            $authorName = $payload['raw_json']['name'] ?? null;
        }

        $feed->scraperId = $scraperId;
        $feed->postId = (string) $payload['post_id'];
        $feed->platform = (string) $payload['platform'];
        $feed->username = $payload['username'] ?? null;
        $feed->authorName = $authorName;
        $feed->description = $payload['description'] ?? null;
        $feed->publicationFailure = null;
        $feed->publicationFailedAt = null;

        // A changed source URL drops the stale public copy so it is re-published (same idempotent
        // strategy as the photo/video upsert below).
        $imageUrl = $payload['image_url'] ?? null;
        if ($feed->imageUrl !== $imageUrl) {
            $feed->publicImagePath = null;
        }
        $feed->imageUrl = $imageUrl;

        $avatarImageUrl = $payload['avatar_image_url'] ?? null;
        if ($feed->avatarImageUrl !== $avatarImageUrl) {
            $feed->publicAvatarImagePath = null;
        }
        $feed->avatarImageUrl = $avatarImageUrl;

        $feed->url = $payload['url'] ?? null;
        $feed->score = isset($payload['score']) ? (int) $payload['score'] : null;
        $feed->datePublished = $this->parseDate($payload['date_published'] ?? null);

        $feed->rawJson = $payload;

        $this->upsertVideos($feed, \is_array($payload['videos'] ?? null) ? $payload['videos'] : []);
        $this->upsertPhotos($feed, \is_array($payload['photos'] ?? null) ? $payload['photos'] : []);
    }

    /**
     * Upserts feed videos by scraper id: existing rows are updated in place (preserving the
     * transcoded Video link), new ones are created, and rows absent from the payload are removed.
     * A changed stream URL for the same scraper id drops the stale Video link for reprocessing.
     *
     * @param array<int, mixed> $videosData
     */
    private function upsertVideos(SocialNetworkFeed $feed, array $videosData): void
    {
        $existingByScraperId = [];
        foreach ($feed->videos as $video) {
            if (null !== $video->scraperId) {
                $existingByScraperId[$video->scraperId] = $video;
            }
        }

        $kept = [];
        foreach ($videosData as $videoData) {
            if (!\is_array($videoData)) {
                continue;
            }

            $scraperId = isset($videoData['id']) ? (int) $videoData['id'] : null;
            $streamUrl = $videoData['stream_url'] ?? null;

            $video = (null !== $scraperId && isset($existingByScraperId[$scraperId]))
                ? $existingByScraperId[$scraperId]
                : new SocialNetworkFeedVideo($feed);

            if ($video->streamUrl !== $streamUrl) {
                $video->video = null;
            }

            $video->scraperId = $scraperId;
            $video->videoType = $videoData['video_type'] ?? null;
            $video->width = isset($videoData['width']) ? (int) $videoData['width'] : null;
            $video->height = isset($videoData['height']) ? (int) $videoData['height'] : null;
            $video->bitrate = isset($videoData['bitrate']) ? (int) $videoData['bitrate'] : null;
            $video->streamUrl = $streamUrl;

            $feed->addVideo($video);
            $kept[spl_object_id($video)] = true;
        }

        foreach ($feed->videos->toArray() as $video) {
            if (!isset($kept[spl_object_id($video)])) {
                $feed->videos->removeElement($video);
            }
        }
    }

    /**
     * Upserts feed photos by scraper id (same strategy as upsertVideos). A changed source URL for
     * the same scraper id drops the stale public copy so it is re-published.
     *
     * NOTE: intentional duplication with upsertVideos — the two entities have divergent field
     * mappings and only these two call sites; a generic helper would add closures/complexity for no
     * real reuse gain (KISS).
     *
     * @param array<int, mixed> $photosData
     */
    private function upsertPhotos(SocialNetworkFeed $feed, array $photosData): void
    {
        $existingByScraperId = [];
        foreach ($feed->photos as $photo) {
            if (null !== $photo->scraperId) {
                $existingByScraperId[$photo->scraperId] = $photo;
            }
        }

        $kept = [];
        foreach ($photosData as $photoData) {
            if (!\is_array($photoData)) {
                continue;
            }

            $scraperId = isset($photoData['id']) ? (int) $photoData['id'] : null;
            $src = $photoData['src'] ?? null;

            $photo = (null !== $scraperId && isset($existingByScraperId[$scraperId]))
                ? $existingByScraperId[$scraperId]
                : new SocialNetworkFeedPhoto($feed);

            if ($photo->src !== $src) {
                $photo->publicSrc = null;
            }

            $photo->scraperId = $scraperId;
            $photo->width = isset($photoData['width']) ? (int) $photoData['width'] : null;
            $photo->height = isset($photoData['height']) ? (int) $photoData['height'] : null;
            $photo->src = $src;

            $feed->addPhoto($photo);
            $kept[spl_object_id($photo)] = true;
        }

        foreach ($feed->photos->toArray() as $photo) {
            if (!isset($kept[spl_object_id($photo)])) {
                $feed->photos->removeElement($photo);
            }
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
