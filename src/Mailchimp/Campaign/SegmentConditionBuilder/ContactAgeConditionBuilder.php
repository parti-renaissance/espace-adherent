<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ContactAgeConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof AdherentZoneFilter
            || $filter instanceof ReferentUserFilter
            || $filter instanceof CommitteeFilter
        ;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var CommitteeFilter|ReferentUserFilter|AdherentZoneFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

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
