<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

/**
 * Verdict returned by {@see MailchimpCampaignSendGuard::evaluate()}.
 *
 * Immutable value object: private constructor + 3 named factories matching {@see SendDecisionEnum}.
 */
class SendDecision
{
    private function __construct(
        public readonly SendDecisionEnum $kind,
        public readonly ?string $reason,
        public readonly ?int $recipientCount,
        public readonly bool $forceSendOnExhaustion = false,
    ) {
    }

    public static function send(int $recipientCount): self
    {
        return new self(SendDecisionEnum::Send, null, $recipientCount);
    }

    public static function retry(string $reason, ?int $recipientCount = null, bool $forceSendOnExhaustion = false): self
    {
        return new self(SendDecisionEnum::Retry, $reason, $recipientCount, $forceSendOnExhaustion);
    }

    public static function abort(string $reason, ?int $recipientCount = null): self
    {
        return new self(SendDecisionEnum::Abort, $reason, $recipientCount);
    }
}
