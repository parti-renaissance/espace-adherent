<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;

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
