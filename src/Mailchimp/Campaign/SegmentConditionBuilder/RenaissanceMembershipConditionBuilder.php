<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Committee\Filter\Enum\RenaissanceMembershipFilterEnum;
use App\Entity\AdherentMessage\Filter\AbstractUserFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Membership\MembershipSourceEnum;

class RenaissanceMembershipConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return ($filter instanceof AudienceFilter || $filter instanceof AbstractUserFilter) && null !== $filter->getRenaissanceMembership();
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
        switch ($filter->getRenaissanceMembership()) {
            case RenaissanceMembershipFilterEnum::ADHERENT_OR_SYMPATHIZER_RE:
                return [[
                    'condition_type' => 'TextMerge',
                    'op' => 'is',
                    'field' => MemberRequest::MERGE_FIELD_SOURCE,
                    'value' => MembershipSourceEnum::RENAISSANCE,
                ]];
            case RenaissanceMembershipFilterEnum::ADHERENT_RE:
                return [
                    [
                        'condition_type' => 'TextMerge',
                        'op' => 'is',
                        'field' => MemberRequest::MERGE_FIELD_SOURCE,
                        'value' => MembershipSourceEnum::RENAISSANCE,
                    ],
                    [
                        'condition_type' => 'DateMerge',
                        'op' => 'blank_not',
                        'field' => MemberRequest::MERGE_FIELD_LAST_MEMBERSHIP_DONATION,
                        'value' => 'is not blank',
                    ],
                ];
            case RenaissanceMembershipFilterEnum::SYMPATHIZER_RE:
                return [
                    [
                        'condition_type' => 'TextMerge',
                        'op' => 'is',
                        'field' => MemberRequest::MERGE_FIELD_SOURCE,
                        'value' => MembershipSourceEnum::RENAISSANCE,
                    ],
                    [
                        'condition_type' => 'DateMerge',
                        'op' => 'blank',
                        'field' => MemberRequest::MERGE_FIELD_LAST_MEMBERSHIP_DONATION,
                        'value' => 'is blank',
                    ],
                ];
            case RenaissanceMembershipFilterEnum::OTHERS_ADHERENT:
                return [
                    [
                        'condition_type' => 'TextMerge',
                        'op' => 'not',
                        'field' => MemberRequest::MERGE_FIELD_SOURCE,
                        'value' => MembershipSourceEnum::RENAISSANCE,
                    ],
                ];
            default:
                return [];
        }
    }
}
