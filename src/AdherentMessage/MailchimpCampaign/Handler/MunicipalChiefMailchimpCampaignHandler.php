<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use AppBundle\Intl\FranceCitiesBundle;

class MunicipalChiefMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
{
    public function handle(AdherentMessageInterface $message): void
    {
        if (!$filter = $message->getFilter()) {
            if (empty($message->getMailchimpCampaigns())) {
                $message->setMailchimpCampaigns([new MailchimpCampaign($message)]);
            }

            return;
        }

        /** @var MunicipalChiefFilter $filter */
        $filter = $message->getFilter();

        $cities = array_values($filter->getCities());

        /** @var MailchimpCampaign[] */
        $campaigns = array_values($message->getMailchimpCampaigns());

        $campaignsToCreateCount = \count($cities);
        $existingCampaignsCount = \count($campaigns);

        for ($i = 0, $limit = max($campaignsToCreateCount, $existingCampaignsCount); $i < $limit; ++$i) {
            if (isset($cities[$i])) {
                if (!isset($campaigns[$i])) {
                    $campaigns[$i] = new MailchimpCampaign($message);
                }

                /** @var MailchimpCampaign $campaign */
                $campaign = $campaigns[$i];
                $campaign->resetFilter();

                $campaign->setCity($cities[$i]);
                $campaign->setLabel(FranceCitiesBundle::getCityNameFromInseeCode($cities[$i]));
            } elseif (isset($campaigns[$i])) {
                unset($campaigns[$i]);
            }
        }

        $message->setMailchimpCampaigns($campaigns);
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof MunicipalChiefAdherentMessage;
    }
}
