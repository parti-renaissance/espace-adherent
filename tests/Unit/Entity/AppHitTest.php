<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity;

use App\Entity\AppHit;
use PHPUnit\Framework\TestCase;

final class AppHitTest extends TestCase
{
    public function testSuspiciousDefaultsToFalse(): void
    {
        $appHit = new AppHit();

        self::assertFalse($appHit->suspicious);
    }

    public function testSuspiciousCanBeSetToTrue(): void
    {
        $appHit = new AppHit();
        $appHit->suspicious = true;

        self::assertTrue($appHit->suspicious);
    }
}
