<?php

declare(strict_types=1);

namespace App\SocialNetwork\Video\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use App\Video\Transcoding\CapacityAwareMessageInterface;
use App\Video\Transcoding\VideoTranscodingMessageInterface;

class TranscodeSocialNetworkVideoCommand implements AsynchronousMessageInterface, LockableMessageInterface, VideoTranscodingMessageInterface, CapacityAwareMessageInterface
{
    public function __construct(
        public readonly int $socialNetworkFeedVideoId,
        public readonly string $sourceUri,
        public readonly int $capacityAttempt = 0,
    ) {
    }

    public function getCapacityAttempt(): int
    {
        return $this->capacityAttempt;
    }

    public function withNextCapacityAttempt(): self
    {
        return new self($this->socialNetworkFeedVideoId, $this->sourceUri, $this->capacityAttempt + 1);
    }

    public function getLockKey(): string
    {
        return 'video_transcode_feed_video_'.$this->socialNetworkFeedVideoId;
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
