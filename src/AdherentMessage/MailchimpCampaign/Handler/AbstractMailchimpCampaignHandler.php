<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

abstract class AbstractMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
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

    abstract protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array;
}
