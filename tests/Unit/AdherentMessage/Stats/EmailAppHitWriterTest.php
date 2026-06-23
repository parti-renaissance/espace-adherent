<?php

declare(strict_types=1);

namespace Tests\App\Unit\AdherentMessage\Stats;

use App\AdherentMessage\Stats\EmailAppHitWriter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class EmailAppHitWriterTest extends TestCase
{
    private const OBJECT_ID = '11111111-1111-4111-8111-111111111111';

    public function testBuildOpenRowShape(): void
    {
        $ts = new \DateTimeImmutable('2024-01-15 10:30:00', new \DateTimeZone('UTC'));

        $row = $this->writer()->buildOpenRow(42, self::OBJECT_ID, $ts);

        self::assertSame(42, $row['adherent_id']);
        self::assertSame('open', $row['event_type']);
        self::assertSame('email', $row['source']);
        self::assertSame('publication', $row['object_type']);
        self::assertSame(self::OBJECT_ID, $row['object_id']);
        self::assertSame('2024-01-15 10:30:00', $row['app_date']);
        self::assertSame(
            hash('sha256', implode('|', [42, 'email', 'open', self::OBJECT_ID, $ts->format('c')])),
            $row['fingerprint']
        );
        self::assertArrayNotHasKey('target_url', $row);
        self::assertArrayNotHasKey('suspicious', $row);
    }

    public function testBuildClickRowShapeAndUrlInFingerprint(): void
    {
        $ts = new \DateTimeImmutable('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
        $url = 'https://parti-renaissance.fr/x';

        $row = $this->writer()->buildClickRow(42, self::OBJECT_ID, $url, $ts);

        self::assertSame('click', $row['event_type']);
        self::assertSame($url, $row['target_url']);
        self::assertSame(
            hash('sha256', implode('|', [42, 'email', 'click', $url, self::OBJECT_ID, $ts->format('c')])),
            $row['fingerprint']
        );
        // Suspicious is not set at build time; clicks are flagged after insert by
        // AppHitRepository::markSuspiciousEmailClicks().
        self::assertArrayNotHasKey('suspicious', $row);
    }

    public function testOpenAndClickFingerprintsDifferForSameContext(): void
    {
        $ts = new \DateTimeImmutable('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
        $writer = $this->writer();

        $open = $writer->buildOpenRow(42, self::OBJECT_ID, $ts);
        $click = $writer->buildClickRow(42, self::OBJECT_ID, 'https://a.fr', $ts);

        self::assertNotSame($open['fingerprint'], $click['fingerprint']);
    }

    public function testIdenticalInputsYieldSameFingerprint(): void
    {
        $ts = new \DateTimeImmutable('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
        $writer = $this->writer();

        self::assertSame(
            $writer->buildClickRow(7, self::OBJECT_ID, 'https://a.fr', $ts)['fingerprint'],
            $writer->buildClickRow(7, self::OBJECT_ID, 'https://a.fr', $ts)['fingerprint']
        );
    }

    public function testDifferentUrlsYieldDifferentClickFingerprints(): void
    {
        $ts = new \DateTimeImmutable('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
        $writer = $this->writer();

        self::assertNotSame(
            $writer->buildClickRow(7, self::OBJECT_ID, 'https://a.fr', $ts)['fingerprint'],
            $writer->buildClickRow(7, self::OBJECT_ID, 'https://b.fr', $ts)['fingerprint']
        );
    }

    private function writer(): EmailAppHitWriter
    {
        // The pure builders do not touch the EntityManager.
        return new EmailAppHitWriter($this->createStub(EntityManagerInterface::class));
    }
}
