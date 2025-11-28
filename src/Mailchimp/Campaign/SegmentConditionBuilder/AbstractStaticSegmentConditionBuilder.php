<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

abstract class AbstractStaticSegmentConditionBuilder extends AbstractConditionBuilder
{
    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return [$this->buildStaticSegmentCondition($this->getSegmentId($campaign->getMessage()->getFilter(), $campaign))];
    }

    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return [];
    }

    abstract protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int;
}
