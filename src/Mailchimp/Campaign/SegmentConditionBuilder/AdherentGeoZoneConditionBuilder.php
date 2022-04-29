<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\AudienceTypeEnum;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentGeoZoneConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentGeoZoneFilter
            || $filter instanceof AudienceFilter
        ;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        $filter = $campaign->getMessage()->getFilter();

        if (AudienceTypeEnum::LEGISLATIVE_CANDIDATE_NEWSLETTER === $campaign->getMailchimpListType()) {
            return $this->buildZoneCondition(
                MemberRequest::MERGE_FIELD_ZONE_CODES,
                $filter->getZone()->getTypeCode(),
                'contains'
            );
        }

        return $this->buildFromFilter($filter);
    }

    /**
     * @param AdherentGeoZoneFilter|AudienceFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return $this->buildZoneCondition(
            MemberRequest::getMergeFieldFromZone($zone = $filter->getZone()),
            sprintf('(%s)', $zone->getCode()),
            'ends'
        );
    }

    protected function buildZoneCondition(string $field, string $value, string $operator): array
    {
        return [[
            'condition_type' => 'TextMerge',
            'op' => $operator,
            'field' => $field,
            'value' => $value,
        ]];
    }
}
