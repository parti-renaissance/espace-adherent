<?php

declare(strict_types=1);

namespace App\Committee\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;

class RefreshCommitteeMembershipsInZoneCommand implements AsynchronousMessageInterface, LockableMessageInterface
{
    private string $zoneCode;

    public function __construct(string $zoneCode)
    {
        $this->zoneCode = $zoneCode;
    }

    public function getZoneCode(): string
    {
        return $this->zoneCode;
    }

    public function getLockKey(): string
    {
        return 'refresh_committee_membership_'.$this->zoneCode;
    }

    public function getLockTtl(): int
    {
        return 60;
    }

    public function isLockBlocking(): bool
    {
        return true;
    }
}
