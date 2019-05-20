<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use AppBundle\Entity\ReferentTag;

class ReferentMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
{
    public function handle(AdherentMessageInterface $message): void
    {
        /** @var ReferentUserFilter $filter */
        if ($message->isSent()) {
            return;
        }

        if (!$filter = $message->getFilter()) {
            if (empty($message->getMailchimpCampaigns())) {
                $message->setMailchimpCampaigns([new MailchimpCampaign($message)]);
            }

            return;
        }

        /** @var ReferentTag[] */
        $filterTags = array_values($filter->getReferentTags());
        /** @var MailchimpCampaign[] */
        $campaigns = array_values($message->getMailchimpCampaigns());

        $tagsCount = \count($filterTags);
        $campaignsCount = \count($campaigns);

        for ($i = 0, $limit = max($tagsCount, $campaignsCount); $i < $limit; ++$i) {
            if (isset($filterTags[$i])) {
                if (!isset($campaigns[$i])) {
                    $campaigns[$i] = new MailchimpCampaign($message);
                }
                $campaigns[$i]->setStaticSegmentId($filterTags[$i]->getExternalId());
                $campaigns[$i]->setLabel($filterTags[$i]->getCode());
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
}
