<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class MandateTypeConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter && $filter->getMandateType();
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AdherentMessageFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $value = $filter->getMandateType();

        return [[
            'condition_type' => 'TextMerge',
            'op' => $filter->includeFilter($value) ? 'contains' : 'notcontain',
            'value' => '"'.ltrim($value, '!').'"',
            'field' => MemberRequest::MERGE_FIELD_MANDATE_TYPES,
        ]];
    }
}
