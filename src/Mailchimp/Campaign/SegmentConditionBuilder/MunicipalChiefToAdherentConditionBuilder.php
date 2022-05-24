<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\FranceCities\FranceCities;
use App\Intl\FranceCitiesBundle;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Exception\InvalidFilterException;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class MunicipalChiefToAdherentConditionBuilder extends AbstractConditionBuilder
{
    private FranceCities $franceCities;

    public function __construct(MailchimpObjectIdMapping $mailchimpObjectIdMapping, FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;

        parent::__construct($mailchimpObjectIdMapping);
    }

    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof MunicipalChiefFilter && $filter->getContactAdherents();
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        if (!$inseeCode = $campaign->getCity()) {
            throw new InvalidFilterException($campaign->getMessage(), '[MunicipalChiefMessage] Message does not have a valid city value');
        }

        $cityName = $this->franceCities->getCityByInseeCode($inseeCode)->getName() ??
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

    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return [];
    }
}
