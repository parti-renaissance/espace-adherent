<?php

declare(strict_types=1);

namespace App\Mailchimp\MailchimpSegment;

use MyCLabs\Enum\Enum;

class MailchimpSegmentTagEnum extends Enum
{
    public const CERTIFIED = 'certifié';
    public const COMMITTEE_VOTER = 'votant';
}
