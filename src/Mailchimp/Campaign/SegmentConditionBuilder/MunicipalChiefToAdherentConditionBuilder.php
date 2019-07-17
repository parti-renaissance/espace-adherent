<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Mailchimp\Exception\InvalidFilterException;
use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;

class MunicipalChiefToAdherentConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof MunicipalChiefFilter && $filter->getContactAdherents();
    }

    public function build(MailchimpCampaign $campaign): array
    {
        if (!$campaign->getCity()) {
            throw new InvalidFilterException(
                $campaign->getMessage(),
                '[MunicipalChiefMessage] Message does not have a valid city value'
            );
        }

        if (!$cityName = FranceCitiesBundle::getCityNameFromInseeCode($campaign->getCity())) {
            throw new InvalidFilterException(
                $campaign->getMessage(),
                sprintf('[MunicipalMessage] Invalid city Name for insee code "%s"', $campaign->getCity())
            );
        }

        return [[
            'condition_type' => 'TextMerge',
            'op' => 'starts',
            'field' => MemberRequest::MERGE_FIELD_CITY,
            'value' => $cityName.' (',
        ]];
    }
}
