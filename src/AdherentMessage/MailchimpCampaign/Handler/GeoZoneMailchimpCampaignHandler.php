<?php

declare(strict_types=1);

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\MessageFilter;

class GeoZoneMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message->getFilter() instanceof MessageFilter;
    }

    /**
     * @param AdherentMessageFilterInterface|MessageFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $filters = [];

        foreach ($filter->getZones() as $zone) {
            $condition = [
                'type' => self::ZONE,
                'value' => $zone,
                'label' => $zone->getNameCode(),
            ];

            if ($cities = $filter->getCityAsArray()) {
                foreach ($cities as $city) {
                    $filters[] = [
                        $condition,
                        [
                            'type' => self::TEXT_MERGE,
                            'value' => $city,
                            'label' => $zone->getCode().' - '.$city,
                        ],
                    ];
                }
            } else {
                $filters[] = [$condition];
            }
        }

        return $filters;
    }
}
