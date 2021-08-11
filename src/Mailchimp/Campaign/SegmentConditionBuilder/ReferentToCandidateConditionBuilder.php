<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Exception\InvalidFilterException;
use App\Mailchimp\Synchronisation\ApplicationRequestTagLabelEnum;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ReferentToCandidateConditionBuilder extends AbstractConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof ReferentUserFilter
            && ($filter->getContactOnlyVolunteers() || $filter->getContactOnlyRunningMates())
        ;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        if (!$campaign->getLabel()) {
            throw new InvalidFilterException($campaign->getMessage(), '[ReferentMessage] Referent message does not have a valid tag code');
        }

        $conditions[] = [
            'condition_type' => 'TextMerge',
            'op' => 'contains',
            'field' => MemberRequest::MERGE_FIELD_REFERENT_TAGS,
            'value' => $campaign->getLabel(),
        ];

        return array_merge($conditions, $this->buildFromFilter($campaign->getMessage()->getFilter()));
    }

    /**
     * @param ReferentUserFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $conditions = [];

        if ($filter->getContactOnlyRunningMates() ^ $filter->getContactOnlyVolunteers()) {
            $conditions[] = $this->buildStaticSegmentCondition(
                $this->mailchimpObjectIdMapping->getApplicationRequestTagIds()[
                $filter->getContactOnlyRunningMates()
                    ? ApplicationRequestTagLabelEnum::RUNNING_MATE
                    : ApplicationRequestTagLabelEnum::VOLUNTEER
                ]
            );
        }

        return $conditions;
    }
}
