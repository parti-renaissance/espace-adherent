<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AbstractAdherentFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

abstract class AbstractStaticSegmentConditionBuilder extends AbstractConditionBuilder
{
    public function supportSegmentFilter(SegmentFilterInterface $filter): bool
    {
        return false;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return [$this->buildStaticSegmentCondition($this->getSegmentId($campaign->getMessage()->getFilter(), $campaign))];
    }

    public function buildFromFilter(AbstractAdherentFilter $filter): array
    {
        return [];
    }

    abstract protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int;
}
