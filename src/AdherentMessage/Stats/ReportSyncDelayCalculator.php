<?php

declare(strict_types=1);

namespace App\AdherentMessage\Stats;

class ReportSyncDelayCalculator
{
    public function calculate(\DateTimeInterface $sentAt): ?int
    {
        $age = new \DateTimeImmutable()->getTimestamp() - $sentAt->getTimestamp();

        $min = 60;
        $hour = 3600;
        $day = 86400;

        return match (true) {
            $age < 1 * $hour => 5 * $min * 1000,   // 5 min
            $age < 6 * $hour => 10 * $min * 1000,  // 10 min
            $age < 3 * $day => 1 * $hour * 1000,   // 1 h
            $age < 14 * $day => 1 * $day * 1000,   // 1 j
            default => null,                       // stop
        };
    }
}
