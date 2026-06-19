<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Message;

use App\Ses\Campaign\Message\SesCampaignMessageInterface;

class FinalizeCampaignAudienceMessage implements SesCampaignMessageInterface
{
    public function __construct(public int $mailchimpCampaignId)
    {
    }
}
