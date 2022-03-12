<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\ReferentAdherentMessage;

class ReferentMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof ReferentAdherentMessage;
    }

    /**
     * @param AdherentMessageFilterInterface|ReferentUserFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $filters = [];

        foreach ($filter->getReferentTags() as $tag) {
            $staticSegmentCondition = [
                'type' => 'static_segment',
                'value' => $tag->getExternalId(),
                'label' => $tag->getCode(),
            ];

            if ($cities = $filter->getCityAsArray()) {
                foreach ($cities as $city) {
                    $filters[] = [
                        $staticSegmentCondition,
                        [
                            'type' => 'text_merge',
                            'value' => $city,
                            'label' => $tag->getCode().' - '.$city,
                        ],
                    ];
                }
            } else {
                $filters[] = [$staticSegmentCondition];
            }
        }

        return $filters;
    }
}
