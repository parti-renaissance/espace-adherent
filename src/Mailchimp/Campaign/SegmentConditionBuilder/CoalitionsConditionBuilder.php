<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\CoalitionsFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;

class CoalitionsConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof CoalitionsFilter;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        return [];
    }
}
