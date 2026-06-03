<?php

declare(strict_types=1);

namespace App\Video\Transcoding\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use App\Video\Transcoding\CapacityAwareMessageInterface;
use App\Video\Transcoding\VideoTranscodingMessageInterface;

class RelaunchVideoTranscodingCommand implements AsynchronousMessageInterface, LockableMessageInterface, VideoTranscodingMessageInterface, CapacityAwareMessageInterface
{
    public function __construct(
        public readonly string $videoUuid,
        public readonly int $capacityAttempt = 0,
    ) {
    }

    public function getCapacityAttempt(): int
    {
        return $this->capacityAttempt;
    }

    public function withNextCapacityAttempt(): self
    {
        return new self($this->videoUuid, $this->capacityAttempt + 1);
    }

    public function getLockKey(): string
    {
        return 'video_transcode_relaunch_'.$this->videoUuid;
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
