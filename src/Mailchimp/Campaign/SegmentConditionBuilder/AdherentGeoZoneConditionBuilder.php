<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AbstractAdherentFilter;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentGeoZoneConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof AdherentGeoZoneFilter
            || $filter instanceof AudienceFilter
        ;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AdherentGeoZoneFilter|AudienceFilter $filter
     */
    public function buildFromFilter(AbstractAdherentFilter $filter): array
    {
        $zone = $filter->getZone();

        return [[
            'condition_type' => 'TextMerge',
            'op' => 'ends',
            'field' => MemberRequest::getMergeFieldFromZone($zone),
            'value' => sprintf('(%s)', $zone->getCode()),
        ]];
    }
}
