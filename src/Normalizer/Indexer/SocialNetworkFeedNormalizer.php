<?php

declare(strict_types=1);

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\VideoStatusEnum;
use App\Utils\VideoUrlBuilder;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SocialNetworkFeedNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    public function __construct(
        private readonly FilesystemOperator $mediaStorage,
        private readonly VideoUrlBuilder $videoUrlBuilder,
        UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($urlGenerator);
    }

    protected function getClassName(): string
    {
        return SocialNetworkFeed::class;
    }

    /** @param SocialNetworkFeed $object */
    protected function getTitle(object $object): string
    {
        return '';
    }

    /** @param SocialNetworkFeed $object */
    protected function getDescription(object $object): ?string
    {
        return $object->description;
    }

    /** @param SocialNetworkFeed $object */
    protected function getDate(object $object): ?\DateTimeInterface
    {
        return $object->datePublished;
    }

    protected function getAuthorObject(object $object): ?Adherent
    {
        return null;
    }

    /** @param SocialNetworkFeed $object */
    protected function getAuthorName(?Adherent $author, object $object): ?string
    {
        return $object->authorName;
    }

    /** @param SocialNetworkFeed $object */
    protected function getAuthorUsername(object $object): ?string
    {
        return $object->username;
    }

    /** @param SocialNetworkFeed $object */
    protected function getAuthorImageUrl(object $object): ?string
    {
        return null === $object->publicAvatarImagePath
            ? null
            : $this->mediaStorage->publicUrl($object->publicAvatarImagePath);
    }

    /** @param SocialNetworkFeed $object */
    protected function getCategory(object $object): ?string
    {
        return ucfirst($object->platform);
    }

    protected function isNational(object $object): bool
    {
        return true;
    }

    /** @param SocialNetworkFeed $object */
    protected function getUrl(object $object): ?string
    {
        if (null === $object->url) {
            return null;
        }

        // Defensive: only expose http(s) links coming from the scraper, never other schemes.
        return \in_array(parse_url($object->url, \PHP_URL_SCHEME), ['http', 'https'], true) ? $object->url : null;
    }

    /** @param SocialNetworkFeed $object */
    protected function getImage(object $object): ?array
    {
        if (null === $object->publicImagePath) {
            return null;
        }

        return [
            'url' => $this->mediaStorage->publicUrl($object->publicImagePath),
            'width' => $object->imageWidth,
            'height' => $object->imageHeight,
        ];
    }

    /** @param SocialNetworkFeed $object */
    protected function getMedia(object $object): ?array
    {
        $items = [];

        foreach ($object->photos as $photo) {
            if (null === $photo->publicSrc) {
                continue;
            }

            $items[] = [
                'type' => 'photo',
                'url' => $this->mediaStorage->publicUrl($photo->publicSrc),
                'width' => $photo->width,
                'height' => $photo->height,
            ];
        }

        $photoCount = \count($items);

        foreach ($object->videos as $feedVideo) {
            $video = $feedVideo->video;

            if (null === $video || VideoStatusEnum::READY !== $video->status) {
                continue;
            }

            $items[] = [
                'type' => 'video',
                'hls_url' => $this->videoUrlBuilder->videoHlsUrl($video),
                'preview_url' => $this->videoUrlBuilder->videoPreviewUrl($video),
                'thumbnail_url' => $this->videoUrlBuilder->videoThumbnailUrl($video),
                'width' => $video->width,
                'height' => $video->height,
                'duration' => $video->duration,
            ];
        }

        return [
            'network' => $object->platform,
            'type' => $this->resolvePostType($photoCount, \count($items) - $photoCount),
            'items' => $items,
        ];
    }

    private function resolvePostType(int $photoCount, int $videoCount): string
    {
        if (0 === $photoCount && 0 === $videoCount) {
            return 'text';
        }

        if (0 === $videoCount) {
            return 1 === $photoCount ? 'photo' : 'photo_carousel';
        }

        if (0 === $photoCount) {
            return 1 === $videoCount ? 'video' : 'video_carousel';
        }

        return 'carousel';
    }
}
