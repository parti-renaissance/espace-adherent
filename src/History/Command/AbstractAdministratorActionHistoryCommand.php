<?php

declare(strict_types=1);

namespace App\History\Command;

use App\History\AdministratorActionHistoryTypeEnum;

abstract class AbstractAdministratorActionHistoryCommand implements AdministratorActionHistoryCommandInterface
{
    final public function __construct(
        public readonly ?int $administratorId,
        public readonly AdministratorActionHistoryTypeEnum $type,
        public readonly ?array $data = null,
        public ?\DateTimeInterface $date = null,
    ) {
        $this->date = $date ?? new \DateTime();
    }
}
