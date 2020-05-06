<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ContactCityConditionBuilder implements SegmentConditionBuilderInterface
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
        $conditions = [];

        if ($city = $campaign->getCity()) {
            $field = is_numeric($city[0])
                ? MemberRequest::MERGE_FIELD_ZIP_CODE
                : MemberRequest::MERGE_FIELD_CITY
            ;

            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'starts',
                'field' => $field,
                'value' => $city,
            ];
        }

        return $conditions;
    }
}
