<?php

declare(strict_types=1);

namespace Tests\App\Integration\Ses\Webhook;

use App\Ses\Webhook\SesEventRecorder;
use App\Ses\Webhook\SesRawEventData;
use Doctrine\DBAL\Connection;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional: exercises the real native UPSERT against the test database (real service resolution + real
 * connection). Proves persistence of the raw payload, the idempotency on sns_message_id and the skip guard.
 */
class SesEventRecorderTest extends AbstractKernelTestCase
{
    private ?Connection $connection = null;
    private ?SesEventRecorder $recorder = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->manager->getConnection();
        $this->connection->executeStatement('DELETE FROM ses_event');
        // The service has no consumer yet (its handler ships in phase 4) so it is inlined out of the test
        // container; instantiate it directly against the real EntityManager to exercise the real UPSERT.
        $this->recorder = new SesEventRecorder($this->manager);
    }

    protected function tearDown(): void
    {
        $this->connection = null;
        $this->recorder = null;

        parent::tearDown();
    }

    public function testRecordInsertsRowWithRawPayloadAndColumns(): void
    {
        $payload = ['MessageId' => 'sns-1', 'Message' => '{"eventType":"Delivery"}', 'Signature' => 'sig'];
        $receivedAt = new \DateTimeImmutable('2024-03-01 09:00:00');

        $this->recorder->record($this->data('sns-1', $payload), $receivedAt);

        $row = $this->connection->fetchAssociative('SELECT * FROM ses_event WHERE sns_message_id = ?', ['sns-1']);

        self::assertIsArray($row);
        self::assertSame('Delivery', $row['event_type']);
        self::assertSame('ses-msg', $row['ses_message_id']);
        self::assertSame('11111111-1111-4111-8111-111111111111', $row['campaign_uuid']);
        self::assertSame('recipient@example.org', $row['recipient']);
        self::assertSame('2024-01-15 10:30:00', $row['occurred_at']);
        self::assertSame('2024-03-01 09:00:00', $row['received_at']);
        // assertEquals (not assertSame): MySQL normalises JSON object key order; content is preserved.
        self::assertEquals($payload, json_decode((string) $row['payload'], true));
    }

    public function testRecordSameSnsMessageIdIsIdempotent(): void
    {
        $receivedAt = new \DateTimeImmutable('2024-03-01 09:00:00');

        $this->recorder->record($this->data('sns-dup'), $receivedAt);
        $this->recorder->record($this->data('sns-dup'), $receivedAt);

        $count = $this->connection->fetchOne('SELECT COUNT(*) FROM ses_event WHERE sns_message_id = ?', ['sns-dup']);

        self::assertSame(1, (int) $count);
    }

    public function testRecordEmptySnsMessageIdWritesNothing(): void
    {
        $this->recorder->record($this->data(''), new \DateTimeImmutable('2024-03-01 09:00:00'));

        self::assertSame(0, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM ses_event'));
    }

    public function testRecordNullableColumnsStoredAsNull(): void
    {
        $data = new SesRawEventData('sns-null', 'Bounce', null, null, null, null, null, ['MessageId' => 'sns-null']);

        $this->recorder->record($data, new \DateTimeImmutable('2024-03-01 09:00:00'));

        $row = $this->connection->fetchAssociative('SELECT * FROM ses_event WHERE sns_message_id = ?', ['sns-null']);

        self::assertIsArray($row);
        self::assertNull($row['ses_message_id']);
        self::assertNull($row['campaign_uuid']);
        self::assertNull($row['adherent_uuid']);
        self::assertNull($row['recipient']);
        self::assertNull($row['occurred_at']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function data(string $snsMessageId, array $payload = ['x' => 1]): SesRawEventData
    {
        return new SesRawEventData(
            $snsMessageId,
            'Delivery',
            'ses-msg',
            '11111111-1111-4111-8111-111111111111',
            '22222222-2222-4222-8222-222222222222',
            'recipient@example.org',
            new \DateTimeImmutable('2024-01-15 10:30:00'),
            $payload,
        );
    }
}
