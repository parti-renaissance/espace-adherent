<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AbstractAdherentFilter;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentRegistrationDateConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof ReferentUserFilter
            || $filter instanceof CommitteeFilter
            || $filter instanceof AdherentZoneFilter
            || $filter instanceof AudienceFilter
        ;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param CommitteeFilter|ReferentUserFilter|AdherentZoneFilter|AudienceFilter $filter
     */
    public function buildFromFilter(AbstractAdherentFilter $filter): array
    {
        $conditions = [];

        if ($registeredSince = $filter->getRegisteredSince()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => MemberRequest::MERGE_FIELD_ADHESION_DATE,
                'value' => $registeredSince->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($registeredUntil = $filter->getRegisteredUntil()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_ADHESION_DATE,
                'value' => $registeredUntil->format(MemberRequest::DATE_FORMAT),
            ];
        }

        return $conditions;
    }
}
