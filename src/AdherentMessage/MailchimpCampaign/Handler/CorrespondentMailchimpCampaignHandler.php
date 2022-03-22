<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CorrespondentAdherentMessage;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Membership\MembershipSourceEnum;

class CorrespondentMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof CorrespondentAdherentMessage && $message->getFilter() instanceof AudienceFilter;
    }

    /**
     * @param AdherentMessageFilterInterface|AdherentGeoZoneFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        return [
            [
                [
                    'type' => self::MAILCHIMP_LIST_TYPE,
                    'value' => MembershipSourceEnum::JEMENGAGE,
                ],
            ],
            [
                [
                    'type' => self::MAILCHIMP_LIST_TYPE,
                    'value' => null, // for main list
                ],
            ],
        ];
    }
}
