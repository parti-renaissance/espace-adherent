<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class DeclaredMandateConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter && !empty($filter->getDeclaredMandates());
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
        $conditions = [];

        foreach ($filter->getDeclaredMandates() as $declaredMandate) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'contains',
                'value' => $declaredMandate,
                'field' => MemberRequest::MERGE_FIELD_DECLARED_MANDATES,
            ];
        }

        return $conditions;
    }
}
