<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Repository\ReferralRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class ReferralRepositoryTest extends AbstractKernelTestCase
{
    /**
     * Smoke test for the native SQL `getScoreboard` query that uses GROUP BY with
     * multiple non-aggregated SELECT columns. Confirms MySQL strict mode
     * (ONLY_FULL_GROUP_BY) accepts the query — all non-aggregated columns must be
     * either in GROUP BY or functionally dependent on a grouped PK.
     */
    public function testGetScoreboardExecutes(): void
    {
        $result = $this->get(ReferralRepository::class)->getScoreboard();

        self::assertIsArray($result);
    }
}
