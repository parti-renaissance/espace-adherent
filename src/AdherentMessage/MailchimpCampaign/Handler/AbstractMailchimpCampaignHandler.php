<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

abstract class AbstractMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
{
    protected const TEXT_MERGE = 'text_merge';
    protected const STATIC_SEGMENT = 'static_segment';
    protected const MAILCHIMP_SEGMENT = 'mailchimp_segment';
    protected const MAILCHIMP_LIST_TYPE = 'mailchimp_list_type';

    public function getPriority(): int
    {
        return 0;
    }

    final public function supports(AdherentMessageInterface $message): bool
    {
        return AdherentMessageInterface::SOURCE_API !== $message->getSource()
            && $this->_supports($message);
    }

    final public function handle(AdherentMessageInterface $message): void
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
                $labels = [];

                foreach ($campaignFilters[$i] as $campaignFilter) {
                    switch ($campaignFilter['type']) {
                        case self::STATIC_SEGMENT:
                            $campaign->setStaticSegmentId($campaignFilter['value']);
                            $campaign->setLabel($campaignFilter['label']);
                            break;
                        case self::TEXT_MERGE:
                            $campaign->setCity($campaignFilter['value']);
                            $campaign->setLabel($campaignFilter['label']);
                            break;
                        case self::MAILCHIMP_SEGMENT:
                            $campaign->addMailchimpSegment($campaignFilter['value']);
                            $labels[] = $campaignFilter['label'];
                            $campaign->setLabel(implode(' - ', $labels));
                            break;
                        case self::MAILCHIMP_LIST_TYPE:
                            $campaign->setMailchimpListType($campaignFilter['value']);
                            break;
                    }
                }
            } elseif (isset($campaigns[$i])) {
                unset($campaigns[$i]);
            }
        }

        $message->setMailchimpCampaigns($campaigns);
    }

    abstract protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array;

    abstract protected function _supports(AdherentMessageInterface $message): bool;
}
