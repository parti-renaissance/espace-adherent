<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Message;

class ReconcileSendErroredRowMessage implements SesCampaignMessageInterface
{
    public function __construct(
        public readonly int $rowId,
        // 0 = first look, after the grace window; 1 = second and last look. Beyond that the row is left
        // quarantined for good and only an alert remains.
        public readonly int $attempt = 0,
    ) {
    }
}
