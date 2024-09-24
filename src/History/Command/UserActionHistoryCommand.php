<?php

namespace App\History\Command;

use App\Entity\Administrator;
use App\History\UserActionHistoryTypeEnum;
use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class UserActionHistoryCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public readonly UuidInterface $adherentUuid,
        public readonly UserActionHistoryTypeEnum $type,
        public readonly ?array $data = null,
        public readonly ?Administrator $administratorId = null,
        public ?\DateTimeInterface $date = null,
    ) {
        $this->date = $date ?? new \DateTime();
    }
}
