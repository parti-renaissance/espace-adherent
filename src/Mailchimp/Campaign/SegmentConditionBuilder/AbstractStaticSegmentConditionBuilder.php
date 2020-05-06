<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

abstract class AbstractStaticSegmentConditionBuilder extends AbstractConditionBuilder
{
    public function build(MailchimpCampaign $campaign): array
    {
        return [$this->buildStaticSegmentCondition($this->getSegmentId($campaign->getMessage()->getFilter(), $campaign))];
    }

    abstract protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int;
}
