<?php

declare(strict_types=1);

namespace Tests\App\Normalizer;

use App\Normalizer\DateTimeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer as BaseDateTimeNormalizer;

class DateTimeNormalizerTest extends TestCase
{
    private DateTimeNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new DateTimeNormalizer(new BaseDateTimeNormalizer());
    }

    public function testDenormalizeStringToImmutableReturnsImmutable(): void
    {
        $date = $this->normalizer->denormalize('2026-07-06T10:00:00+00:00', \DateTimeImmutable::class);

        $this->assertInstanceOf(\DateTimeImmutable::class, $date);
        $this->assertSame('2026-07-06T10:00:00+00:00', $date->format(\DateTimeInterface::RFC3339));
    }

    public function testDenormalizeStringToInterfaceKeepsMutableDefault(): void
    {
        $date = $this->normalizer->denormalize('2026-07-06T10:00:00+00:00', \DateTimeInterface::class);

        $this->assertInstanceOf(\DateTime::class, $date);
    }

    public function testDenormalizeStringToDateTimeReturnsMutable(): void
    {
        $date = $this->normalizer->denormalize('2026-07-06T10:00:00+00:00', \DateTime::class);

        $this->assertInstanceOf(\DateTime::class, $date);
        $this->assertSame('2026-07-06T10:00:00+00:00', $date->format(\DateTimeInterface::RFC3339));
    }
}
