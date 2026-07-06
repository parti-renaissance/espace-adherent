<?php

declare(strict_types=1);

namespace App\Ses\Unsubscribe;

use App\Entity\Adherent;

class UnsubscribeContext
{
    public function __construct(
        public readonly Adherent $adherent,
        public readonly ?int $memberId,
        public readonly ?string $messageUuid,
    ) {
    }
}
