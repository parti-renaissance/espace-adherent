<?php

declare(strict_types=1);

namespace App\SocialNetwork\Image\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use App\SocialNetwork\Image\FeedImagePublishingMessageInterface;

class PublishSocialNetworkFeedPhotoCommand implements AsynchronousMessageInterface, LockableMessageInterface, FeedImagePublishingMessageInterface
{
    public function __construct(
        public readonly int $photoId,
        public readonly string $src,
    ) {
    }

    public function getLockKey(): string
    {
        return 'feed_photo_'.sha1($this->src);
    }

    public function getLockTtl(): int
    {
        return 600;
    }

    public function isLockBlocking(): bool
    {
        return true;
    }
}
