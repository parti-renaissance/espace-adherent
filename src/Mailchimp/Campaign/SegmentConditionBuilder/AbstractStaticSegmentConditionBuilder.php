<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;

abstract class AbstractStaticSegmentConditionBuilder extends AbstractConditionBuilder
{
    public function build(MailchimpCampaign $campaign): array
    {
        return [$this->buildStaticSegmentCondition($this->getSegmentId($campaign->getMessage()->getFilter()))];
    }

    abstract protected function getSegmentId(AdherentMessageFilterInterface $filter): int;
}
