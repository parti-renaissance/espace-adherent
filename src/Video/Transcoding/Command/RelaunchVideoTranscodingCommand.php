<?php

declare(strict_types=1);

namespace App\Video\Transcoding\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use App\Video\Transcoding\VideoTranscodingMessageInterface;

class RelaunchVideoTranscodingCommand implements AsynchronousMessageInterface, LockableMessageInterface, VideoTranscodingMessageInterface
{
    public function __construct(
        public readonly string $videoUuid,
    ) {
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
