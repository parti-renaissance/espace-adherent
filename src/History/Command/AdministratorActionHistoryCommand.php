<?php

declare(strict_types=1);

namespace App\History\Command;

use App\History\AdministratorActionHistoryTypeEnum;
use App\Messenger\Message\AsynchronousMessageInterface;

class AdministratorActionHistoryCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public readonly ?int $administratorId,
        public readonly AdministratorActionHistoryTypeEnum $type,
        public readonly ?array $data = null,
        public ?\DateTimeInterface $date = null,
    ) {
        $this->date = $date ?? new \DateTime();
    }
}
