<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Message;

class FinalizeCampaignAudienceMessage implements MailchimpAudienceMessageInterface
{
    public function __construct(public int $mailchimpCampaignId)
    {
    }
}
