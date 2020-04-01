<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Entity\AdherentMessage\SenatorAdherentMessage;

class AdherentZoneMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof DeputyAdherentMessage
            || $message instanceof SenatorAdherentMessage
        ;
    }

    /**
     * @param AdherentMessageFilterInterface|AdherentZoneFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $tag = $filter->getReferentTag();

        $staticSegmentCondition = [
            'type' => 'static_segment',
            'value' => $tag->getExternalId(),
            'label' => $tagLabel = $tag->getCode(),
        ];

        $filters = [];

        if ($cities = $filter->getCityAsArray()) {
            foreach ($cities as $city) {
                $filters[] = [
                    $staticSegmentCondition,
                    [
                        'type' => 'text_merge',
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
