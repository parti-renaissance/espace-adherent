<?php

declare(strict_types=1);

namespace App\Committee;

use Ramsey\Uuid\UuidInterface;

class FollowCommitteesBulkResult
{
    /**
     * @param array<int, array{uuid: UuidInterface, committeeId: int}> $newMemberships
     * @param array<int, array{uuid: UuidInterface, committeeId: int}> $removedMemberships
     */
    public function __construct(
        public readonly int $newMembershipCount,
        public readonly array $newMemberships,
        public readonly array $removedMemberships,
    ) {
    }
}
