<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AbstractUserFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class RenaissanceMembershipConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return ($filter instanceof AudienceFilter || $filter instanceof AbstractUserFilter) && null !== $filter->isRenaissanceMembership();
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
            'condition_type' => 'DateMerge',
            'op' => $filter->isRenaissanceMembership() ? 'blank_not' : 'blank',
            'field' => MemberRequest::MERGE_FIELD_LAST_MEMBERSHIP_DONATION,
            'value' => $filter->isRenaissanceMembership() ? 'is not blank' : 'is blank',
        ]];
    }
}
