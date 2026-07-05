<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook\Handler;

use App\Ses\Webhook\Command\ProcessSesEventCommand;
use App\Ses\Webhook\Command\RecordSesRawEventCommand;
use App\Ses\Webhook\Handler\RecordSesRawEventHandler;
use App\Ses\Webhook\SesEventRecorder;
use App\Ses\Webhook\SesRawEventData;
use App\Ses\Webhook\SesRawEventExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Invariant (raw-first): the raw event is recorded BEFORE the business processing is handed off, the hand-off
 * carries the stored event id (ProcessSesEventCommand), and it is skipped when nothing was stored (record
 * failed, or no idempotency key) — so the whole message is retried, never partially processed.
 */
final class RecordSesRawEventHandlerTest extends TestCase
{
    private const PAYLOAD = ['MessageId' => 'sns-1', 'Message' => '{"eventType":"Delivery"}'];

    public function testRecordsRawThenHandsOffProcessingById(): void
    {
        $calls = [];

        $extractor = $this->createMock(SesRawEventExtractor::class);
        $extractor
            ->expects(self::once())
            ->method('extract')
            ->with(self::PAYLOAD)
            ->willReturn($this->data('sns-1'))
        ;

        $recorder = $this->createMock(SesEventRecorder::class);
        $recorder
            ->expects(self::once())
            ->method('record')
            ->with(self::isInstanceOf(SesRawEventData::class), self::isInstanceOf(\DateTimeImmutable::class))
            ->willReturnCallback(static function () use (&$calls): void { $calls[] = 'record'; })
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (object $c): bool => $c instanceof ProcessSesEventCommand && 'sns-1' === $c->snsMessageId))
            ->willReturnCallback(static function (object $c) use (&$calls): Envelope {
                $calls[] = 'process';

                return new Envelope($c);
            })
        ;

        new RecordSesRawEventHandler($extractor, $recorder, $bus)(new RecordSesRawEventCommand(self::PAYLOAD, new \DateTimeImmutable()));

        self::assertSame(['record', 'process'], $calls);
    }

    public function testProcessingIsNotHandedOffWhenRecordFails(): void
    {
        $extractor = $this->createMock(SesRawEventExtractor::class);
        $extractor
            ->expects(self::once())
            ->method('extract')
            ->with(self::PAYLOAD)
            ->willReturn($this->data('sns-1'))
        ;

        $recorder = $this->createMock(SesEventRecorder::class);
        $recorder
            ->expects(self::once())
            ->method('record')
            ->with(self::isInstanceOf(SesRawEventData::class), self::isInstanceOf(\DateTimeImmutable::class))
            ->willThrowException(new \RuntimeException('database unavailable'))
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::never())
            ->method('dispatch')
        ;

        $this->expectException(\RuntimeException::class);

        new RecordSesRawEventHandler($extractor, $recorder, $bus)(new RecordSesRawEventCommand(self::PAYLOAD, new \DateTimeImmutable()));
    }

    public function testProcessingIsNotHandedOffWithoutIdempotencyKey(): void
    {
        $extractor = $this->createMock(SesRawEventExtractor::class);
        $extractor
            ->expects(self::once())
            ->method('extract')
            ->with(self::PAYLOAD)
            ->willReturn($this->data(''))
        ;

        $recorder = $this->createMock(SesEventRecorder::class);
        $recorder
            ->expects(self::once())
            ->method('record')
            ->with(self::isInstanceOf(SesRawEventData::class), self::isInstanceOf(\DateTimeImmutable::class))
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::never())
            ->method('dispatch')
        ;

        new RecordSesRawEventHandler($extractor, $recorder, $bus)(new RecordSesRawEventCommand(self::PAYLOAD, new \DateTimeImmutable()));
    }

    private function data(string $snsMessageId): SesRawEventData
    {
        return new SesRawEventData($snsMessageId, 'Delivery', null, null, null, null, null, self::PAYLOAD);
    }
}
