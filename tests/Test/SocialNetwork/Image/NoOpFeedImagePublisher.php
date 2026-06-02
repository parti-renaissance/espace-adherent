<?php

declare(strict_types=1);

namespace Tests\App\Test\SocialNetwork\Image;

use App\SocialNetwork\Image\Storage\FeedImagePublisherInterface;
use App\SocialNetwork\Image\Storage\PublishedImage;

/**
 * Dev/test publisher: returns a synthetic public path without touching GCS or the public bucket.
 */
class NoOpFeedImagePublisher implements FeedImagePublisherInterface
{
    public function expectedPath(string $sourceGcsUri): string
    {
        return 'social-feed/'.sha1($sourceGcsUri).'.jpg';
    }

    public function publish(string $sourceGcsUri): PublishedImage
    {
        return new PublishedImage($this->expectedPath($sourceGcsUri));
    }
}
