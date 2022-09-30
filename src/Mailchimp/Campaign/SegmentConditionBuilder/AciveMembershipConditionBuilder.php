<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AciveMembershipConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter && null !== $filter->isActiveMembership();
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AudienceFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return [[
            'condition_type' => 'Date',
            'op' => $filter->isActiveMembership() ? 'blank_not' : 'blank',
            'field' => MemberRequest::MERGE_FIELD_LAST_MEMBERSHIP_DONATION,
            'value' => $filter->isActiveMembership() ? 'is not blank' : 'is blank',
        ]];
    }
}
