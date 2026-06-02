<?php

declare(strict_types=1);

namespace App\SocialNetwork\Image\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use App\SocialNetwork\Image\FeedImagePublishingMessageInterface;

class PublishSocialNetworkFeedImagesCommand implements AsynchronousMessageInterface, LockableMessageInterface, FeedImagePublishingMessageInterface
{
    public function __construct(public readonly int $feedId)
    {
    }

    public function getLockKey(): string
    {
        return 'feed_images_'.$this->feedId;
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
