<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback\Message;

use App\Mailchimp\CampaignMessageInterface;

class SendMandrillFallbackChunkMessage implements CampaignMessageInterface
{
    public function __construct(
        public readonly int $campaignId,
        public readonly int $chunkNumber,
        public readonly string $renderedHtml,
    ) {
    }
}
