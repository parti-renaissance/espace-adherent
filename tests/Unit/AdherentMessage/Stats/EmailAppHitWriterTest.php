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
        // Homogeneous row shape: target_url is present but null on an open (a click carries the URL).
        self::assertNull($row['target_url']);
        // Unclassified open (default): not suspicious, no provenance — same as the pre-detector behaviour.
        self::assertSame(0, $row['suspicious']);
        self::assertNull($row['raw']);
    }

    public function testBuildOpenRowCarriesSuspiciousFlagAndEncodedProvenance(): void
    {
        $ts = new \DateTimeImmutable('2024-01-15 10:30:00', new \DateTimeZone('UTC'));

        $row = $this->writer()->buildOpenRow(42, self::OBJECT_ID, $ts, true, ['reliability' => 'unreliable']);

        self::assertSame(1, $row['suspicious']);
        // The JSON column must receive a scalar: provenance is encoded here, not left as an array.
        self::assertSame('{"reliability":"unreliable"}', $row['raw']);
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
        // A click inserts as non-suspicious (0); a same-second burst is flagged after insert by
        // AppHitRepository::markSuspiciousEmailClicks(). raw stays null (no reliability provenance on a click).
        self::assertSame(0, $row['suspicious']);
        self::assertNull($row['raw']);
    }

    public function testOpenAndClickRowsShareTheSameColumnSet(): void
    {
        $ts = new \DateTimeImmutable('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
        $writer = $this->writer();

        $openKeys = array_keys($writer->buildOpenRow(42, self::OBJECT_ID, $ts));
        $clickKeys = array_keys($writer->buildClickRow(42, self::OBJECT_ID, 'https://a.fr', $ts));

        sort($openKeys);
        sort($clickKeys);
        // insertHits derives its columns from the first row only: identical key sets keep mixed
        // open+click batches from silently dropping a column or binding NULL into a NOT NULL one.
        self::assertSame($openKeys, $clickKeys);
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
