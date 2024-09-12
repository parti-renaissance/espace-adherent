<?php

namespace App\History\Command;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\UserActionHistoryTypeEnum;

class UserActionHistoryCommand
{
    public function __construct(
        public readonly Adherent $user,
        public readonly UserActionHistoryTypeEnum $type,
        public readonly ?array $data = null,
        public readonly ?Administrator $impersonificator = null,
        public ?\DateTimeInterface $date = null,
    ) {
        $this->date = $date ?? new \DateTime();
    }
}
