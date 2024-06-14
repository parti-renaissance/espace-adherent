<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Mailchimp\Campaign\AudienceTypeEnum;

class LegislativeCandidateMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof LegislativeCandidateAdherentMessage && $message->getFilter() instanceof AudienceFilter;
    }

    /**
     * @param AdherentMessageFilterInterface|AudienceFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $audienceType = $filter->getAudienceType();
        $conditions = [];

        if (null === $audienceType || AudienceTypeEnum::ADHERENT === $audienceType) {
            $conditions[] = [[
                'type' => self::MAILCHIMP_LIST_TYPE,
                'value' => null, // for main list
            ]];
        }

        return $conditions;
    }
}
