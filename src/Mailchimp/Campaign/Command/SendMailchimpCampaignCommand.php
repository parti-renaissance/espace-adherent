<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Command;

use App\Mailchimp\CampaignMessageInterface;

class SendMailchimpCampaignCommand implements CampaignMessageInterface
{
    public function __construct(public int $campaignId)
    {
    }
}
