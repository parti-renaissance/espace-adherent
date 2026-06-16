<?php

declare(strict_types=1);

namespace Tests\App\Unit\Formation\Moodle\Repair;

use App\Formation\Moodle\Repair\DuplicateAccountResolver;
use App\Formation\Moodle\Repair\RepairStatus;
use PHPUnit\Framework\TestCase;

class DuplicateAccountResolverTest extends TestCase
{
    private DuplicateAccountResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DuplicateAccountResolver();
    }

    public function testKeepsOldestAccountAndDeletesDuplicateHoldingTheCurrentEmail(): void
    {
        $plan = $this->resolver->resolve('new@example.com', [
            ['id' => 100, 'username' => 'old@example.com', 'email' => 'old@example.com', 'timecreated' => 1000],
            ['id' => 200, 'username' => 'new@example.com', 'email' => 'new@example.com', 'timecreated' => 2000],
        ]);

        self::assertSame(RepairStatus::REPAIR, $plan->status);
        self::assertSame(100, $plan->keepMoodleId);
        self::assertSame(200, $plan->deleteMoodleId);
        self::assertSame('new@example.com', $plan->newEmail);
    }

    public function testMatchesCurrentEmailCaseInsensitivelyAndDedupesById(): void
    {
        $plan = $this->resolver->resolve('New@Example.com', [
            ['id' => 100, 'username' => 'old@example.com', 'email' => 'old@example.com', 'timecreated' => 1000],
            ['id' => 200, 'username' => 'new@example.com', 'email' => 'new@example.com', 'timecreated' => 2000],
            ['id' => 200, 'username' => 'new@example.com', 'email' => 'new@example.com', 'timecreated' => 2000],
        ]);

        self::assertSame(RepairStatus::REPAIR, $plan->status);
        self::assertSame(100, $plan->keepMoodleId);
        self::assertSame(200, $plan->deleteMoodleId);
    }

    public function testSingleAccountIsHealthy(): void
    {
        $plan = $this->resolver->resolve('new@example.com', [
            ['id' => 100, 'username' => 'new@example.com', 'email' => 'new@example.com', 'timecreated' => 1000],
        ]);

        self::assertSame(RepairStatus::HEALTHY, $plan->status);
        self::assertNull($plan->keepMoodleId);
    }

    public function testTwoAccountsOnTheCurrentEmailNeedsManualReview(): void
    {
        $plan = $this->resolver->resolve('new@example.com', [
            ['id' => 100, 'username' => 'new@example.com', 'email' => 'new@example.com', 'timecreated' => 1000],
            ['id' => 200, 'username' => 'new@example.com', 'email' => 'new@example.com', 'timecreated' => 2000],
        ]);

        self::assertSame(RepairStatus::MANUAL, $plan->status);
    }

    public function testNoAccountOnTheCurrentEmailNeedsManualReview(): void
    {
        $plan = $this->resolver->resolve('new@example.com', [
            ['id' => 100, 'username' => 'old@example.com', 'email' => 'old@example.com', 'timecreated' => 1000],
            ['id' => 200, 'username' => 'older@example.com', 'email' => 'older@example.com', 'timecreated' => 500],
        ]);

        self::assertSame(RepairStatus::MANUAL, $plan->status);
    }

    public function testRefusesWhenTheCurrentEmailAccountIsOlderThanTheOther(): void
    {
        $plan = $this->resolver->resolve('new@example.com', [
            ['id' => 100, 'username' => 'new@example.com', 'email' => 'new@example.com', 'timecreated' => 1000],
            ['id' => 200, 'username' => 'old@example.com', 'email' => 'old@example.com', 'timecreated' => 2000],
        ]);

        self::assertSame(RepairStatus::MANUAL, $plan->status);
    }
}
