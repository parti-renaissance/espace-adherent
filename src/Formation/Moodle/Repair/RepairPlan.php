<?php

declare(strict_types=1);

namespace App\Formation\Moodle\Repair;

class RepairPlan
{
    public function __construct(
        public readonly RepairStatus $status,
        public readonly string $reason,
        public readonly ?int $keepMoodleId = null,
        public readonly ?int $deleteMoodleId = null,
        public readonly ?string $newEmail = null,
    ) {
    }

    public function isRepairable(): bool
    {
        return RepairStatus::REPAIR === $this->status;
    }
}
