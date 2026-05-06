<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Message;

use App\Mailchimp\CampaignMessageInterface;

class PrepareCampaignAudienceMessage implements CampaignMessageInterface
{
    public function __construct(
        public int $mailchimpCampaignId,
        public int $lockedById,
    ) {
    }
}
