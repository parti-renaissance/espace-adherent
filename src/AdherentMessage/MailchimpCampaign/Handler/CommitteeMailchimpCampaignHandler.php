<?php

declare(strict_types=1);

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Scope\ScopeEnum;

class CommitteeMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return ScopeEnum::ANIMATOR === $message->getInstanceScope()
            && ($message->getFilter() instanceof CommitteeFilter || $message->getFilter() instanceof MessageFilter);
    }

    /**
     * @param AdherentMessageFilterInterface|CommitteeFilter|MessageFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $committee = $filter->getCommittee();
        $committeeLabel = substr($committee->getUuidAsString(), 0, 8);

        $filters = [];

        $staticSegmentCondition = [
            'type' => self::STATIC_SEGMENT,
            'value' => $committee->getMailchimpId(),
            'label' => $committeeLabel,
        ];

        if ($cities = $filter->getCityAsArray()) {
            foreach ($cities as $city) {
                $filters[] = [
                    $staticSegmentCondition,
                    [
                        'type' => self::TEXT_MERGE,
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
