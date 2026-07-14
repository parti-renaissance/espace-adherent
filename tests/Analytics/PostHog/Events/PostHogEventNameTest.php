<?php

declare(strict_types=1);

namespace Tests\App\Analytics\PostHog\Events;

use App\Analytics\PostHog\Events\PostHogEventName;
use PHPUnit\Framework\TestCase;

class PostHogEventNameTest extends TestCase
{
    public function testCountEventsMatchesSpec(): void
    {
        $this->assertCount(30, PostHogEventName::cases());
    }

    public function testValueUsesSnakeCase(): void
    {
        foreach (PostHogEventName::cases() as $case) {
            $this->assertMatchesRegularExpression(
                '/^[a-z][a-z0-9_]+$/',
                $case->value,
                "Event {$case->name} must be snake_case",
            );
        }
    }

    public function testConsentGrantedValue(): void
    {
        $this->assertSame('consent_granted', PostHogEventName::CONSENT_GRANTED->value);
    }
}
