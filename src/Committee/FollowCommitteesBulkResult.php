<?php

declare(strict_types=1);

namespace App\Committee;

use Symfony\Component\Uid\Uuid;

class FollowCommitteesBulkResult
{
    /**
     * @param array<int, array{uuid: Uuid, committeeId: int}> $newMemberships
     * @param array<int, array{uuid: Uuid, committeeId: int}> $removedMemberships
     */
    public function __construct(
        public readonly int $newMembershipCount,
        public readonly array $newMemberships,
        public readonly array $removedMemberships,
    ) {
    }
}
