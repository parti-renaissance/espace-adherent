<?php

declare(strict_types=1);

namespace Tests\App\Normalizer;

use App\Normalizer\DataCleaner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class DataCleanerTest extends TestCase
{
    private DataCleaner $cleaner;

    protected function setUp(): void
    {
        $this->cleaner = new DataCleaner();
    }

    public function testCleanNullsKeysOutsideAllowList(): void
    {
        $cleaned = $this->cleaner->clean(
            ['name' => 'kept', 'secret' => 'leaked'],
            ['name'],
        );

        self::assertSame('kept', $cleaned['name']);
        self::assertNull($cleaned['secret']);
    }

    public function testCleanRecursesIntoNestedAllowList(): void
    {
        $cleaned = $this->cleaner->clean(
            ['address' => ['city' => 'Paris', 'street' => '10 rue X']],
            ['address', 'address' => ['city']],
        );

        self::assertSame('Paris', $cleaned['address']['city']);
        self::assertNull($cleaned['address']['street']);
    }

    public function testCleanTruncatesAtSuffixKeys(): void
    {
        $cleaned = $this->cleaner->clean(
            ['begin_at' => '2026-05-22T18:30:00+02:00'],
            ['begin_at'],
        );

        self::assertSame('2026-05-22', $cleaned['begin_at']);
    }

    public function testCleanTruncatesExtraDateKeys(): void
    {
        $cleaned = $this->cleaner->clean(
            ['date' => new \DateTime('2026-05-22 18:30:00')],
            ['date'],
            ['date'],
        );

        self::assertSame('2026-05-22', $cleaned['date']);
    }

    public function testCleanDoesNotTruncateNonDateKeysWithoutExtraDateKeys(): void
    {
        $cleaned = $this->cleaner->clean(
            ['date' => '2026-05-22T18:30:00+02:00'],
            ['date'],
        );

        self::assertSame('2026-05-22T18:30:00+02:00', $cleaned['date']);
    }
}
