<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Message;

class PrepareCampaignAudienceMessage implements MailchimpAudienceMessageInterface
{
    public function __construct(
        public int $mailchimpCampaignId,
        public int $lockedById,
    ) {
    }
}
