<?php

declare(strict_types=1);

namespace App\SocialNetwork\Image\Storage;

interface FeedImagePublisherInterface
{
    /**
     * Deterministic public path a published source would have (no I/O).
     */
    public function expectedPath(string $sourceGcsUri): string;

    /**
     * Copies the scraper source image to our public bucket (idempotent) and returns its public path
     * together with its dimensions when the content was downloaded.
     *
     * @throws \InvalidArgumentException on a permanent failure (bucket not allowed, non-image, oversized)
     */
    public function publish(string $sourceGcsUri): PublishedImage;
}
