<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

/**
 * Verdict returned by {@see PostSendDeliveryGuard::evaluate()}.
 *
 * Immutable value object: private constructor + named factories matching {@see DeliveryDecisionEnum}.
 * Mirrors {@see SendDecision} for consistency.
 */
class DeliveryDecision
{
    private function __construct(
        public readonly DeliveryDecisionEnum $kind,
        public readonly ?string $reason,
        public readonly ?int $emailsSent,
    ) {
    }

    public static function ok(?int $emailsSent): self
    {
        return new self(DeliveryDecisionEnum::Ok, null, $emailsSent);
    }

    public static function pending(string $reason): self
    {
        return new self(DeliveryDecisionEnum::Pending, $reason, null);
    }

    public static function failed(string $reason, ?int $emailsSent): self
    {
        return new self(DeliveryDecisionEnum::Failed, $reason, $emailsSent);
    }

    public static function unverifiable(string $reason): self
    {
        return new self(DeliveryDecisionEnum::Unverifiable, $reason, null);
    }

    public static function stillSending(string $reason): self
    {
        return new self(DeliveryDecisionEnum::StillSending, $reason, null);
    }

    public static function notSending(string $reason): self
    {
        return new self(DeliveryDecisionEnum::NotSending, $reason, null);
    }
}
