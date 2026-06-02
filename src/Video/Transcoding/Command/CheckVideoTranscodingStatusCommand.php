<?php

declare(strict_types=1);

namespace App\Video\Transcoding\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Video\Transcoding\VideoTranscodingMessageInterface;

class CheckVideoTranscodingStatusCommand implements AsynchronousMessageInterface, VideoTranscodingMessageInterface
{
    public function __construct(
        public readonly string $videoUuid,
        public readonly string $jobName,
        public readonly int $startedAt,
    ) {
    }
}
