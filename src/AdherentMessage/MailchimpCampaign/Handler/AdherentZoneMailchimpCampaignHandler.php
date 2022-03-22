<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;

class AdherentZoneMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    protected function _supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof DeputyAdherentMessage
            || $message instanceof SenatorAdherentMessage
            || $message instanceof LegislativeCandidateAdherentMessage
        ;
    }

    /**
     * @param AdherentMessageFilterInterface|AdherentZoneFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $tag = $filter->getReferentTag();

        $staticSegmentCondition = [
            'type' => self::STATIC_SEGMENT,
            'value' => $tag->getExternalId(),
            'label' => $tagLabel = $tag->getCode(),
        ];

        $filters = [];

        if ($cities = $filter->getCityAsArray()) {
            foreach ($cities as $city) {
                $filters[] = [
                    $staticSegmentCondition,
                    [
                        'type' => self::TEXT_MERGE,
                        'value' => $city,
                        'label' => $tagLabel.' - '.$city,
                    ],
                ];
            }
        } else {
            $filters[] = [$staticSegmentCondition];
        }

        return $filters;
    }
}
