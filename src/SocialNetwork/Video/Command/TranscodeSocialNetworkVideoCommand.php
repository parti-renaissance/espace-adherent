<?php

declare(strict_types=1);

namespace App\SocialNetwork\Video\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use App\Video\Transcoding\VideoTranscodingMessageInterface;

class TranscodeSocialNetworkVideoCommand implements AsynchronousMessageInterface, LockableMessageInterface, VideoTranscodingMessageInterface
{
    public function __construct(
        public readonly int $socialNetworkFeedVideoId,
        public readonly string $sourceUri,
    ) {
    }

    public function getLockKey(): string
    {
        return 'video_transcode_'.sha1($this->sourceUri);
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
