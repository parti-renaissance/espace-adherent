<?php

namespace App\History\Command;

use App\Entity\Administrator;
use App\History\UserActionHistoryTypeEnum;
use Ramsey\Uuid\UuidInterface;

class UserActionHistoryCommand
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
