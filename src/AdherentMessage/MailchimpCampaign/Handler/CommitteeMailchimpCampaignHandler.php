<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;

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
        $filters = [];

        foreach ($filter->getCityAsArray() as $city) {
            $filters[] = [[
                'type' => 'text_merge',
                'value' => $city,
                'label' => $city,
            ]];
        }

        return $filters;
    }
}
