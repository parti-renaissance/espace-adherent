<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesEventType;
use PHPUnit\Framework\TestCase;

final class SesEventTypeTest extends TestCase
{
    public function testResolvesFromEventType(): void
    {
        self::assertSame(SesEventType::Delivery, SesEventType::fromDecodedEvent(['eventType' => 'Delivery']));
    }

    public function testResolvesFromNotificationTypeWhenEventTypeAbsent(): void
    {
        // Legacy direct identity notification format.
        self::assertSame(SesEventType::Bounce, SesEventType::fromDecodedEvent(['notificationType' => 'Bounce']));
    }

    public function testEventTypeTakesPrecedenceOverNotificationType(): void
    {
        self::assertSame(SesEventType::Complaint, SesEventType::fromDecodedEvent([
            'eventType' => 'Complaint',
            'notificationType' => 'Bounce',
        ]));
    }

    public function testReturnsNullWhenNoTypePresent(): void
    {
        self::assertNull(SesEventType::fromDecodedEvent([]));
    }

    public function testReturnsNullForUnknownType(): void
    {
        self::assertNull(SesEventType::fromDecodedEvent(['eventType' => 'Rendering Failure']));
    }

    public function testReturnsNullForNonStringType(): void
    {
        // A malformed payload must not raise a TypeError on tryFrom().
        self::assertNull(SesEventType::fromDecodedEvent(['eventType' => ['Delivery']]));
    }
}
