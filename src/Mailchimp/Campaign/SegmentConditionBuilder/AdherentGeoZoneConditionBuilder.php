<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Address\AddressInterface;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Entity\Geo\Zone;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentGeoZoneConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter;
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
