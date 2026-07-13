<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Message;

class SendSesCampaignChunkMessage implements SesCampaignMessageInterface
{
    public function __construct(
        public readonly int $campaignId,
        public readonly int $chunkNumber,
        // Consecutive re-dispatches caused by SES throttling; reset once the chunk makes progress.
        // Defaulted so in-flight messages and the initial fan-out (TriggerSesCampaignHandler) stay valid.
        public readonly int $throttleAttempt = 0,
    ) {
    }
}
