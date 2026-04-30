<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

readonly class BatchResult
{
    public function __construct(
        public int $inserted,
        public int $lastIdBefore,
        public int $lastIdAfter,
    ) {
    }

    public function hasMore(int $batchSize): bool
    {
        return $this->inserted >= $batchSize;
    }
}
