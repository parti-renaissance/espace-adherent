<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\MailchimpSegment\MailchimpSegmentTagEnum;

class CertifiedConditionBuilder extends AbstractStaticSegmentConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter && null !== $filter->isCertified();
    }

    /**
     * @param AudienceFilter $filter
     */
    protected function getSegmentId(AdherentMessageFilterInterface $filter, MailchimpCampaign $campaign): int
    {
        return $this->getSegmentTagIds()[MailchimpSegmentTagEnum::CERTIFIED];
    }
}
