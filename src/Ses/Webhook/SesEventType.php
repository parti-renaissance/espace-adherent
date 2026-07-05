<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

enum SesEventType: string
{
    case Delivery = 'Delivery';
    case DeliveryDelay = 'DeliveryDelay';
    case Reject = 'Reject';
    case Bounce = 'Bounce';
    case Complaint = 'Complaint';
    case Open = 'Open';
    case Click = 'Click';

    public static function fromDecodedEvent(array $decoded): ?self
    {
        $raw = $decoded['eventType'] ?? $decoded['notificationType'] ?? null;

        return \is_string($raw) ? self::tryFrom($raw) : null;
    }
}
