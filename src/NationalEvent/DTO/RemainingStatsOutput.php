<?php

namespace App\NationalEvent\DTO;

final class RemainingStatsOutput
{
    public function __construct(
        public int $pendingCount,
        public ?string $message = null,
    ) {
    }
}
