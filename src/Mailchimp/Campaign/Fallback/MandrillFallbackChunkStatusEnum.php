<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback;

enum MandrillFallbackChunkStatusEnum: string
{
    case Pending = 'pending';
    case Sending = 'sending';
    case Sent = 'sent';
    case NeedsReview = 'needs_review';
}
