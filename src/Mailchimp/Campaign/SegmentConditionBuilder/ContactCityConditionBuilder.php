<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ContactCityConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof ReferentUserFilter
            || $filter instanceof MessageFilter
            || $filter instanceof CommitteeFilter;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        $conditions = [];

        if ($city = $campaign->getCity()) {
            $field = is_numeric($city[0])
                ? MemberRequest::MERGE_FIELD_ZIP_CODE
                : MemberRequest::MERGE_FIELD_CITY;

            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'starts',
                'field' => $field,
                'value' => $city,
            ];
        }

        return $conditions;
    }

    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return [];
    }
}
