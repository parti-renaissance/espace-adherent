<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class JMECommitteeConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        if (null !== $campaign->getMailchimpListType()) {
            return [];
        }

        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AdherentMessageFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $conditions = [];

        if ($committee = $filter->getCommittee()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => MemberRequest::MERGE_FIELD_COMMITTEE,
                'value' => $committee->getUuidAsString(),
            ];
        }

        if (null !== $filter->getIsCommitteeMember()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => $filter->getIsCommitteeMember() ? 'blank_not' : 'blank',
                'field' => MemberRequest::MERGE_FIELD_COMMITTEE,
            ];
        }

        return $conditions;
    }
}
