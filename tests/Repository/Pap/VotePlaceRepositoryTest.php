<?php

declare(strict_types=1);

namespace Tests\App\Repository\Pap;

use App\Repository\Pap\VotePlaceRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class VotePlaceRepositoryTest extends AbstractKernelTestCase
{
    /**
     * Smoke test for the native SQL `findNear` query that uses GROUP BY on the
     * primary key with non-aggregated SELECT columns. Confirms MySQL strict mode
     * (ONLY_FULL_GROUP_BY) accepts the query through functional dependency on vp.id.
     */
    public function testFindNearExecutes(): void
    {
        // Paris coordinates — content does not matter, just executes the query.
        $result = $this->get(VotePlaceRepository::class)->findNear(48.8566, 2.3522);

        self::assertIsArray($result);
    }
}
