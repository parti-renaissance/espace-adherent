<?php

declare(strict_types=1);

namespace App\SocialNetwork\Publication\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;

class CheckSocialNetworkFeedPublicationCommand implements AsynchronousMessageInterface, LockableMessageInterface
{
    public function __construct(
        public readonly int $feedId,
        public readonly int $startedAt,
    ) {
    }

    public function getLockKey(): string
    {
        return 'feed_publish_'.$this->feedId;
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
