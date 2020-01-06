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
        if (!$inseeCode = $campaign->getCity()) {
            throw new InvalidFilterException($campaign->getMessage(), '[MunicipalChiefMessage] Message does not have a valid city value');
        }

        $cityName = FranceCitiesBundle::getCityNameFromInseeCode($inseeCode) ??
            FranceCitiesBundle::SPECIAL_CITY_ZONES[$inseeCode] ?? null;

        if (!$cityName) {
            throw new InvalidFilterException($campaign->getMessage(), sprintf('[MunicipalMessage] Invalid city Name for insee code "%s"', $inseeCode));
        }

        $conditions = [[
            'condition_type' => 'TextMerge',
            'op' => 'starts',
            'field' => MemberRequest::MERGE_FIELD_CITY,
            'value' => isset(FranceCitiesBundle::SPECIAL_CITY_ZONES[$inseeCode]) ? $cityName : $cityName.' (',
        ]];

        /** @var MunicipalChiefFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

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
