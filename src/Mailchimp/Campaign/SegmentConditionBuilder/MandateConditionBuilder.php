<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class MandateConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter && null !== $filter->getMandateType();
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
        $mandates = $filter->getMandates();
        $conditions = [];

        foreach ($mandates as $mandate) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'value' => $mandate,
                'field' => MemberRequest::MERGE_FIELD_MANDATE_TYPE,
            ];
        }

        return $conditions;
    }
}
