<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\JecouteFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class JecouteConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof JecouteFilter;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var JecouteFilter $filter */
        $filter = $campaign->getMessage()->getFilter();
        $conditions = [];

        $zone = $filter->getZone();

        $conditions[] = [
            'condition_type' => 'TextMerge',
            'op' => 'is',
            'field' => MemberRequest::getMergeCodeFieldFromZone($zone),
            'value' => $zone->getCode(),
        ];

        if ($filter->getPostalCode()) {
            $conditions[] = [
                'condition_type' => 'TextMerge',
                'op' => 'starts',
                'field' => MemberRequest::MERGE_FIELD_ZIP_CODE,
                'value' => $filter->getPostalCode(),
            ];
        }

        return $conditions;
    }
}
