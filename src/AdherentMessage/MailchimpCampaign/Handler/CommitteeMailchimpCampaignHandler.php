<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;

class CommitteeMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof CommitteeAdherentMessage;
    }

    /**
     * @param AdherentMessageFilterInterface|CommitteeFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $committee = $filter->getCommittee();
        $committeeLabel = substr($committee->getUuidAsString(), 0, 8);

        $filters = [];

        $staticSegmentCondition = [
            'type' => 'static_segment',
            'value' => $committee->getMailchimpId(),
            'label' => $committeeLabel,
        ];

        if ($cities = $filter->getCityAsArray()) {
            foreach ($cities as $city) {
                $filters[] = [
                    $staticSegmentCondition,
                    [
                        'type' => 'text_merge',
                        'value' => $city,
                        'label' => $committeeLabel.' - '.$city,
                    ],
                ];
            }
        } else {
            $filters[] = [$staticSegmentCondition];
        }

        return $filters;
    }
}
