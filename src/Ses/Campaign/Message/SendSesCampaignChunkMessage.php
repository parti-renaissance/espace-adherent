<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Message;

class SendSesCampaignChunkMessage implements SesCampaignMessageInterface
{
    public function __construct(
        public readonly int $campaignId,
        public readonly int $chunkNumber,
    ) {
    }
}
