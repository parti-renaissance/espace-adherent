<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class SendPushChunkCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public string $notificationClassName,
        public string $title,
        public string $body,
        public ?string $scope,
        public array $data,
        public array $tokens,
        public string $chunkKey,
    ) {
    }
}
