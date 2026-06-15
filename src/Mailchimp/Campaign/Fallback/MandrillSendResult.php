<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback;

class MandrillSendResult
{
    public function __construct(
        public readonly int $sent,
        public readonly int $queued,
        public readonly int $rejected,
        public readonly int $invalid,
        /** @var list<string> */
        public readonly array $rejectedEmails = [],
    ) {
    }

    public function total(): int
    {
        return $this->sent + $this->queued + $this->rejected + $this->invalid;
    }

    public function rejectionRate(): float
    {
        $total = $this->total();

        if (0 === $total) {
            return 0.0;
        }

        return ($this->rejected + $this->invalid) / $total;
    }
}
