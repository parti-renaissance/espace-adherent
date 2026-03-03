<?php

declare(strict_types=1);

namespace App\Mailchimp\Contact;

enum MarketingConsentStatusEnum: string
{
    case CONFIRMED = 'confirmed';   // already verified — skip double opt-in
    case DENIED = 'denied';         // explicit unsubscribe
    case UNKNOWN = 'unknown';       // no consent recorded — transactional only

    public static function fromContactStatus(?string $status): self
    {
        return match ($status) {
            ContactStatusEnum::SUBSCRIBED => self::CONFIRMED,
            ContactStatusEnum::UNSUBSCRIBED => self::DENIED,
            default => self::UNKNOWN,
        };
    }
}
