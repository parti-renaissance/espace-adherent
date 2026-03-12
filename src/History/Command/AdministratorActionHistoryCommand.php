<?php

declare(strict_types=1);

namespace App\History\Command;

use App\History\AdministratorActionHistoryTypeEnum;

class AdministratorActionHistoryCommand implements AdministratorActionHistoryCommandInterface
{
    public function __construct(
        public readonly ?int $administratorId,
        public readonly AdministratorActionHistoryTypeEnum $type,
        public readonly ?array $data = null,
        public ?\DateTimeImmutable $date = null,
    ) {
        $this->date = $date ?? new \DateTimeImmutable();
    }
}
