<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\Processor\SesEventProcessorInterface;
use App\Ses\Webhook\SesEventDispatcher;
use App\Ses\Webhook\SesEventType;
use App\Ses\Webhook\SesPayloadReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SesEventDispatcherTest extends TestCase
{
    /**
     * @param list<string> $expected labels of the processors whose process() was invoked, in order
     */
    #[DataProvider('provideRouting')]
    public function testRoutesEventTypeToProcessors(string $eventType, array $expected): void
    {
        $processed = $this->dispatch(['Message' => json_encode(['eventType' => $eventType])]);

        self::assertSame($expected, $processed);
    }

    public static function provideRouting(): iterable
    {
        yield 'delivery' => ['Delivery', ['delivery']];
        yield 'delivery delay' => ['DeliveryDelay', ['delivery_delay']];
        yield 'open' => ['Open', ['engagement']];
        yield 'click' => ['Click', ['engagement']];
        yield 'reject' => ['Reject', ['reject']];
        // Config-set feedback: email-keyed suppression + per-member attribution, both run in-process.
        yield 'bounce' => ['Bounce', ['suppression', 'feedback_attribution']];
        yield 'complaint' => ['Complaint', ['suppression', 'feedback_attribution']];
    }

    public function testLegacyNotificationWithoutEventTypeRunsGlobalOnly(): void
    {
        // notificationType (no eventType, no tags) → email-keyed suppression alone (attribution needs tags).
        $processed = $this->dispatch(['Message' => json_encode(['notificationType' => 'Bounce'])]);

        self::assertSame(['suppression'], $processed);
    }

    #[DataProvider('provideNonRoutableEventTypes')]
    public function testRunsNothingForNonRoutableEvents(string $eventType): void
    {
        $processed = $this->dispatch(['Message' => json_encode(['eventType' => $eventType])]);

        self::assertSame([], $processed);
    }

    public static function provideNonRoutableEventTypes(): iterable
    {
        yield 'send' => ['Send'];
        yield 'unknown type' => ['Rendering Failure'];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<string>
     */
    private function dispatch(array $payload): array
    {
        $processed = [];

        $processors = [
            $this->processorRecording('delivery', $processed, false, SesEventType::Delivery),
            $this->processorRecording('delivery_delay', $processed, false, SesEventType::DeliveryDelay),
            $this->processorRecording('reject', $processed, false, SesEventType::Reject),
            $this->processorRecording('engagement', $processed, false, SesEventType::Open, SesEventType::Click),
            $this->processorRecording('suppression', $processed, true, SesEventType::Bounce, SesEventType::Complaint),
            $this->processorRecording('feedback_attribution', $processed, false, SesEventType::Bounce, SesEventType::Complaint),
        ];

        new SesEventDispatcher(new SesPayloadReader(), $processors)->dispatch($payload);

        return $processed;
    }

    /**
     * @param list<string> $processed collector the processor appends its label to when process() runs
     */
    private function processorRecording(string $label, array &$processed, bool $directNotification, SesEventType ...$supported): SesEventProcessorInterface
    {
        $processor = $this->createStub(SesEventProcessorInterface::class);
        $processor->method('supports')->willReturnCallback(static fn (SesEventType $type): bool => \in_array($type, $supported, true));
        $processor->method('supportsDirectNotification')->willReturn($directNotification);
        $processor->method('process')->willReturnCallback(static function () use (&$processed, $label): void {
            $processed[] = $label;
        });

        return $processor;
    }
}
