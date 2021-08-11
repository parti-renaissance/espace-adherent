<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AbstractAdherentFilter;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ContactAgeConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentZoneFilter
            || $filter instanceof AdherentGeoZoneFilter
            || $filter instanceof ReferentUserFilter
            || $filter instanceof CommitteeFilter
            || $filter instanceof AudienceFilter
        ;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param CommitteeFilter|ReferentUserFilter|AdherentZoneFilter|AdherentGeoZoneFilter|AudienceFilter $filter
     */
    public function buildFromFilter(AbstractAdherentFilter $filter): array
    {
        $conditions = [];

        $now = new \DateTimeImmutable('now');

        if ($minAge = $filter->getAgeMin()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_BIRTHDATE,
                'value' => $now->modify(sprintf('-%d years', $minAge))->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($maxAge = $filter->getAgeMax()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => MemberRequest::MERGE_FIELD_BIRTHDATE,
                'value' => $now->modify(sprintf('-%d years', $maxAge))->format(MemberRequest::DATE_FORMAT),
            ];
        }

        return $conditions;
    }
}
