<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\Ses\Webhook\SesEventType;

interface SesEventProcessorInterface
{
    public function supports(SesEventType $type): bool;

    /**
     * Whether this processor also applies to the legacy direct identity notification (no "eventType",
     * no campaign tags). Only email-keyed processing (suppression) does; per-member processing needs tags.
     */
    public function supportsDirectNotification(): bool;

    /**
     * @param array<string, mixed> $payload the full SNS envelope, reloaded from the ses_event source of truth
     */
    public function process(array $payload): void;
}
