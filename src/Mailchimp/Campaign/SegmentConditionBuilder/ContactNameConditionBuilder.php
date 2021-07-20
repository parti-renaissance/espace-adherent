<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AbstractElectedRepresentativeFilter;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\CoalitionsFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ContactNameConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof ReferentUserFilter
            || $filter instanceof AdherentZoneFilter
            || $filter instanceof AdherentGeoZoneFilter
            || $filter instanceof CommitteeFilter
            || ($filter instanceof MunicipalChiefFilter && !$filter->getContactNewsletter())
            || $filter instanceof AbstractElectedRepresentativeFilter
            || $filter instanceof CoalitionsFilter
            || $filter instanceof AudienceFilter
        ;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var MunicipalChiefFilter|ReferentUserFilter|AdherentZoneFilter|AdherentGeoZoneFilter|ReferentElectedRepresentativeFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

        $conditions = [];

        if ($filter->getGender()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => MemberRequest::MERGE_FIELD_GENDER,
                'value' => $filter->getGender(),
            ];
        }

        if ($filter->getFirstName()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => MemberRequest::MERGE_FIELD_FIRST_NAME,
                'value' => $filter->getFirstName(),
            ];
        }

        if ($filter->getLastName()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'is',
                'field' => MemberRequest::MERGE_FIELD_LAST_NAME,
                'value' => $filter->getLastName(),
            ];
        }

        return $conditions;
    }
}
