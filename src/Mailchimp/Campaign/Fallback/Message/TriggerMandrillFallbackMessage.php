<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback\Message;

use App\Mailchimp\CampaignMessageInterface;

class TriggerMandrillFallbackMessage implements CampaignMessageInterface
{
    public const string FORCE_FALLBACK_SUBJECT_TOKEN = '[[MANDRILL-FALLBACK-TEST]]';

    public function __construct(public readonly int $campaignId)
    {
    }
}
