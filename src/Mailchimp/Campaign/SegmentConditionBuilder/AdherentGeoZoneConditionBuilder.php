<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Address\AddressInterface;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Entity\Geo\Zone;
use App\Mailchimp\Campaign\AudienceTypeEnum;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentGeoZoneConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter;
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

        if ($campaign->getZone()) {
            return $this->buildFromZone($campaign->getZone());
        }

        return $this->buildFromFilter($filter);
    }

    /**
     * @param AdherentMessageFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $zones = [];
        if (1 === $filter->getZones()->count()) {
            $zones[] = $filter->getZones()->first();
        }

        if ($zone = $filter->getZone()) {
            $zones[] = $zone;
        }

        $zones = array_unique($zones);

        $conditions = [];

        foreach ($zones as $zone) {
            $conditions[] = $this->buildFromZone($zone);
        }

        return $conditions;
    }

    private function buildFromZone(Zone $zone): array
    {
        if (Zone::FDE_CODE === $zone->getCode()) {
            return $this->buildZoneCondition(
                MemberRequest::MERGE_FIELD_ZONE_COUNTRY,
                \sprintf('(%s)', AddressInterface::FRANCE),
                'notcontain'
            );
        }

        return $this->buildZoneCondition(
            MemberRequest::getMergeFieldFromZone($zone),
            \sprintf(' (%s)', $zone->getCode()),
            'contains'
        );
    }

    protected function buildZoneCondition(string $field, string $value, string $operator): array
    {
        return [
            'condition_type' => 'TextMerge',
            'op' => $operator,
            'field' => $field,
            'value' => $value,
        ];
    }
}
