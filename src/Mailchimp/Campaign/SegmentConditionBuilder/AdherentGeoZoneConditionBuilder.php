<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentGeoZoneConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof AdherentGeoZoneFilter;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var AdherentGeoZoneFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

        $zone = $filter->getZone();

        return [[
            'condition_type' => 'TextMerge',
            'op' => 'ends',
            'field' => MemberRequest::getMergeFieldFromZone($zone),
            'value' => sprintf('(%s)', $zone->getCode()),
        ]];
    }
}
