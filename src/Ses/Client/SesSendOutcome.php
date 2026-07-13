<?php

declare(strict_types=1);

namespace App\Ses\Client;

/**
 * Result of a SES SendEmail call for one recipient.
 *
 * Two terminal outcomes are represented here: a successful send (carrying the SES MessageId) and a
 * permanent rejection (a 4xx that will never succeed on retry, e.g. an unverified/invalid address).
 * Transport failures (network, 5xx, 429 throttling) are NOT outcomes — they propagate as exceptions: a 429
 * (provably rejected) lets the caller reopen the row and retry, while an ambiguous failure (network / 5xx /
 * empty MessageId, where SES may already have sent) makes the caller quarantine the row (SendErrored).
 */
class SesSendOutcome
{
    private function __construct(
        public readonly bool $sent,
        public readonly ?string $messageId,
        public readonly ?string $rejectionReason,
    ) {
    }

    public static function sent(string $messageId): self
    {
        return new self(true, $messageId, null);
    }

    public static function rejected(string $reason): self
    {
        return new self(false, null, $reason);
    }

    public function isSent(): bool
    {
        return $this->sent;
    }
}
