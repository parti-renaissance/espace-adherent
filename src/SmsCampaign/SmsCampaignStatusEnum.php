<?php

namespace App\SmsCampaign;

use MyCLabs\Enum\Enum;

final class SmsCampaignStatusEnum extends Enum
{
    public const DRAFT = 'draft';
    public const SENDING = 'sending';
    public const DONE = 'done';
}
