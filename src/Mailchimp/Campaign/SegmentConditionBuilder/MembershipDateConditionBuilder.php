<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AbstractUserFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\DateUtils;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class MembershipDateConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AbstractUserFilter
            || $filter instanceof AudienceFilter;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        if (null !== $campaign->getMailchimpListType()) {
            return [];
        }

        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AbstractUserFilter|AudienceFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $conditions = [];

        if ($firstMembershipSince = $filter->firstMembershipSince) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => MemberRequest::MERGE_FIELD_FIRST_MEMBERSHIP_DONATION,
                'value' => DateUtils::adjustDate($firstMembershipSince, false)->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($firstMembershipBefore = $filter->firstMembershipBefore) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_FIRST_MEMBERSHIP_DONATION,
                'value' => DateUtils::adjustDate($firstMembershipBefore, true)->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($lastMembershipSince = $filter->getLastMembershipSince()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => MemberRequest::MERGE_FIELD_LAST_MEMBERSHIP_DONATION,
                'value' => DateUtils::adjustDate($lastMembershipSince, false)->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($lastMembershipBefore = $filter->getLastMembershipBefore()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_LAST_MEMBERSHIP_DONATION,
                'value' => DateUtils::adjustDate($lastMembershipBefore, true)->format(MemberRequest::DATE_FORMAT),
            ];
        }

        return $conditions;
    }
}
