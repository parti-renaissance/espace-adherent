<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class MandateTypeConditionBuilder implements SegmentConditionBuilderInterface
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
        switch ($filter->getMandateType()) {
            case MandateTypeEnum::TYPE_ALL:
                return [[
                    'condition_type' => 'TextMerge',
                    'op' => 'blank_not',
                    'field' => MemberRequest::MERGE_FIELD_MANDATE_TYPE,
                ]];
            case MandateTypeEnum::TYPE_NONE:
                return [[
                    'condition_type' => 'TextMerge',
                    'op' => 'blank',
                    'field' => MemberRequest::MERGE_FIELD_MANDATE_TYPE,
                ]];
            case MandateTypeEnum::TYPE_LOCAL:
                return [[
                    'condition_type' => 'TextMerge',
                    'op' => 'contains',
                    'value' => MandateTypeEnum::TYPE_LOCAL,
                    'field' => MemberRequest::MERGE_FIELD_MANDATE_TYPE,
                ]];
            case MandateTypeEnum::TYPE_NATIONAL:
                return [[
                    'condition_type' => 'TextMerge',
                    'op' => 'contains',
                    'value' => MandateTypeEnum::TYPE_NATIONAL,
                    'field' => MemberRequest::MERGE_FIELD_MANDATE_TYPE,
                ]];
            default:
                return [];
        }
    }
}
