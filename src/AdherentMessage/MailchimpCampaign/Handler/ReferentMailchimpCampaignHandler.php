<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;

class ReferentMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
{
    public function handle(AdherentMessageInterface $message): void
    {
        if (!$filter = $message->getFilter()) {
            if (empty($message->getMailchimpCampaigns())) {
                $message->setMailchimpCampaigns([new MailchimpCampaign($message)]);
            }

            return;
        }

        $campaignFilters = $this->getCampaignFilters($filter);

        /** @var MailchimpCampaign[] */
        $campaigns = array_values($message->getMailchimpCampaigns());

        $campaignsToCreateCount = \count($campaignFilters);
        $existingCampaignsCount = \count($campaigns);

        for ($i = 0, $limit = max($campaignsToCreateCount, $existingCampaignsCount); $i < $limit; ++$i) {
            if (isset($campaignFilters[$i])) {
                if (!isset($campaigns[$i])) {
                    $campaigns[$i] = new MailchimpCampaign($message);
                }

                /** @var MailchimpCampaign $campaign */
                $campaign = $campaigns[$i];
                $campaign->resetFilter();

                foreach ($campaignFilters[$i] as $campaignFilter) {
                    if ('static_segment' === $campaignFilter['type']) {
                        $campaign->setStaticSegmentId($campaignFilter['value']);
                        $campaign->setLabel($campaignFilter['label']);
                    } elseif ('text_merge' === $campaignFilter['type']) {
                        $campaign->setCity($campaignFilter['value']);
                        $campaign->setLabel($campaignFilter['label']);
                    }
                }
            } elseif (isset($campaigns[$i])) {
                unset($campaigns[$i]);
            }
        }

        $message->setMailchimpCampaigns($campaigns);
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof ReferentAdherentMessage;
    }

    private function getCampaignFilters(ReferentUserFilter $filter): array
    {
        $filters = [];

        foreach ($filter->getReferentTags() as $tag) {
            $staticSegmentCondition = [
                'type' => 'static_segment',
                'value' => $tag->getExternalId(),
                'label' => $tag->getCode(),
            ];

            if ($cities = $filter->getCityAsArray()) {
                foreach ($filter->getCityAsArray() as $city) {
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
